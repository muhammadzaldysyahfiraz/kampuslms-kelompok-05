#!/usr/bin/env bash
# ==============================================================================
# KampusLMS - Authorization & IDOR Security Verification Test Suite
# SI2514024 Pemrograman Web - Pekan 07 & Persiapan UTS (Bagian 3)
#
# Memeriksa 8 Titik Rawan IDOR & Otorisasi:
# 1. Mahasiswa membaca submission orang lain -> 403 Forbidden
# 2. Mahasiswa membaca submission milik sendiri -> 200 OK
# 3. Mahasiswa menilai submission via API -> 403 Forbidden
# 4. Dosen A memodifikasi tugas di MK Dosen B (Cross-Lecturer) -> 403 Forbidden
# 5. Dosen A memodifikasi tugas di MK sendiri -> 200/302 Success
# 6. Cross-Course Scoped Binding Assignment -> 404 Not Found
# 7. Cross-Course Scoped Binding Material -> 404 Not Found
# 8. Mahasiswa / Dosen mengakses master /users -> 403 Forbidden
# ==============================================================================

BASE_URL="${BASE_URL:-http://127.0.0.1:8000}"
API_URL="${BASE_URL}/api/v1"
COOKIE_JAR_MHS=$(mktemp)
COOKIE_JAR_DOSEN=$(mktemp)

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

PASSED_COUNT=0
FAILED_COUNT=0

cleanup() {
    rm -f "$COOKIE_JAR_MHS" "$COOKIE_JAR_DOSEN"
}
trap cleanup EXIT

print_header() {
    echo -e "\n${BLUE}==============================================================================${NC}"
    echo -e "${CYAN}  $1${NC}"
    echo -e "${BLUE}==============================================================================${NC}"
}

assert_status() {
    local desc="$1"
    local expected="$2"
    local actual="$3"

    if [ "$actual" -eq "$expected" ]; then
        echo -e "  [${GREEN}PASS${NC}] $desc (HTTP $actual)"
        PASSED_COUNT=$((PASSED_COUNT + 1))
    else
        echo -e "  [${RED}FAIL${NC}] $desc (Diharapkan: HTTP $expected, Didapat: HTTP $actual)"
        FAILED_COUNT=$((FAILED_COUNT + 1))
    fi
}

echo -e "${YELLOW}Menjalankan Pengujian Otorisasi & IDOR KampusLMS pada: ${BASE_URL}${NC}"

# ------------------------------------------------------------------------------
# TAHAP 1: Inisialisasi Sesi & Token
# ------------------------------------------------------------------------------
print_header "TAHAP 1: Inisialisasi Sesi Web dan Token Sanctum"

# Sesi Mahasiswa Demo
curl -s -c "$COOKIE_JAR_MHS" "${BASE_URL}/switch-role/mahasiswa" -L -o /dev/null
echo -e "  [*] Sesi Web Mahasiswa terhubung."

# Sesi Dosen Demo
curl -s -c "$COOKIE_JAR_DOSEN" "${BASE_URL}/switch-role/dosen" -L -o /dev/null
echo -e "  [*] Sesi Web Dosen terhubung."

# Token API Mahasiswa Demo
TOKEN_MHS=$(curl -s -X POST "${API_URL}/auth/login" \
    -H "Content-Type: application/json" \
    -d '{"email":"mahasiswa@kampuslms.test","password":"password"}' | grep -o '"token":"[^"]*' | cut -d'"' -f4)

# ------------------------------------------------------------------------------
# TAHAP 2: Uji IDOR Akses Submission
# ------------------------------------------------------------------------------
print_header "TAHAP 2: Uji Titik Rawan IDOR Submission"

# 1. Mahasiswa membuka submission orang lain (Submission 79)
CODE_SUBM_OTHER=$(curl -s -b "$COOKIE_JAR_MHS" -o /dev/null -w "%{http_code}" "${BASE_URL}/submissions/79")
assert_status "Mahasiswa mengakses submission mahasiswa lain (ID 79) -> Ditahan 403" 403 "$CODE_SUBM_OTHER"

# 2. Mahasiswa membuka submission milik sendiri (Submission 1)
CODE_SUBM_SELF=$(curl -s -b "$COOKIE_JAR_MHS" -o /dev/null -w "%{http_code}" "${BASE_URL}/submissions/1")
assert_status "Mahasiswa mengakses submission miliknya sendiri (ID 1) -> Diizinkan 200" 200 "$CODE_SUBM_SELF"

# 3. Mahasiswa mencoba menilai submission via API
if [ -n "$TOKEN_MHS" ]; then
    CODE_GRADE_ATTEMPT=$(curl -s -o /dev/null -w "%{http_code}" -X PUT "${API_URL}/submissions/1/grade" \
        -H "Authorization: Bearer $TOKEN_MHS" \
        -H "Content-Type: application/json" \
        -H "Accept: application/json" \
        -d '{"score":100,"feedback":"Manipulasi nilai sendiri"}')
    assert_status "Mahasiswa mencoba memberi nilai tugas -> Ditahan 403" 403 "$CODE_GRADE_ATTEMPT"
fi

# ------------------------------------------------------------------------------
# TAHAP 3: Uji Cross-Lecturer IDOR pada Tugas & Materi
# ------------------------------------------------------------------------------
print_header "TAHAP 3: Uji Titik Rawan IDOR Antar-Dosen (Cross-Lecturer)"

# Dosen demo (User 2) mencoba mengedit Tugas 1 (diampu Dosen 4)
CODE_EDIT_ASSIGN_OTHER=$(curl -s -b "$COOKIE_JAR_DOSEN" -o /dev/null -w "%{http_code}" -X PUT "${BASE_URL}/assignments/1" \
    -H "Accept: application/json" \
    -d "title=Bajak+Tugas")
assert_status "Dosen mengedit tugas MK milik dosen lain (ID 1) -> Ditahan 403" 403 "$CODE_EDIT_ASSIGN_OTHER"

# Dosen demo mencoba mengedit Tugas 4 (MK 2 diampu sendiri)
CODE_EDIT_ASSIGN_OWN=$(curl -s -b "$COOKIE_JAR_DOSEN" -o /dev/null -w "%{http_code}" -X PUT "${BASE_URL}/assignments/4" \
    -H "Accept: application/json" \
    -d "title=Tugas+Pertemuan+1")
# Controller mengembalikan 200 (jika JSON) atau 302 redirect
if [ "$CODE_EDIT_ASSIGN_OWN" -eq 200 ] || [ "$CODE_EDIT_ASSIGN_OWN" -eq 302 ]; then
    assert_status "Dosen mengedit tugas MK miliknya sendiri (ID 4) -> Berhasil" "$CODE_EDIT_ASSIGN_OWN" "$CODE_EDIT_ASSIGN_OWN"
else
    assert_status "Dosen mengedit tugas MK miliknya sendiri (ID 4) -> Berhasil" 200 "$CODE_EDIT_ASSIGN_OWN"
fi

# ------------------------------------------------------------------------------
# TAHAP 4: Uji Implicit Scoped Route Binding (Cross-Course IDOR)
# ------------------------------------------------------------------------------
print_header "TAHAP 4: Uji Scoped Route Binding (Route::scopeBindings())"

# Akses Assignment 1 (milik Course 1) melalui Course 2
CODE_SCOPED_ASSIGN=$(curl -s -b "$COOKIE_JAR_DOSEN" -o /dev/null -w "%{http_code}" "${BASE_URL}/courses/2/assignments/1")
assert_status "Scoped Binding /courses/2/assignments/1 (beda MK) -> Menghasilkan 404" 404 "$CODE_SCOPED_ASSIGN"

# Akses Material 1 (milik Course 1) melalui Course 2
CODE_SCOPED_MAT=$(curl -s -b "$COOKIE_JAR_DOSEN" -o /dev/null -w "%{http_code}" "${BASE_URL}/courses/2/materials/1")
assert_status "Scoped Binding /courses/2/materials/1 (beda MK) -> Menghasilkan 404" 404 "$CODE_SCOPED_MAT"

# ------------------------------------------------------------------------------
# TAHAP 5: Uji Proteksi Modul Pengguna (Role-Based Access Control)
# ------------------------------------------------------------------------------
print_header "TAHAP 5: Uji Batasan Hak Akses Pengguna (/users)"

CODE_USERS_MHS=$(curl -s -b "$COOKIE_JAR_MHS" -o /dev/null -w "%{http_code}" "${BASE_URL}/users")
assert_status "Mahasiswa mencoba mengakses /users -> Ditahan 403" 403 "$CODE_USERS_MHS"

CODE_USERS_DOSEN=$(curl -s -b "$COOKIE_JAR_DOSEN" -o /dev/null -w "%{http_code}" "${BASE_URL}/users")
assert_status "Dosen mencoba mengakses /users -> Ditahan 403" 403 "$CODE_USERS_DOSEN"

# ------------------------------------------------------------------------------
# REKAPITULASI
# ------------------------------------------------------------------------------
echo -e "\n${BLUE}==============================================================================${NC}"
echo -e "  Hasil Uji Otorisasi: ${GREEN}${PASSED_COUNT} Berhasil${NC}, ${RED}${FAILED_COUNT} Gagal${NC}"
echo -e "${BLUE}==============================================================================${NC}"

if [ "$FAILED_COUNT" -eq 0 ]; then
    echo -e "${GREEN}✓ Seluruh titik rawan IDOR & otorisasi terlindungi dengan sempurna!${NC}\n"
    exit 0
else
    echo -e "${RED}✗ Ditemukan kebocoran otorisasi, silakan periksa rincian di atas!${NC}\n"
    exit 1
fi
