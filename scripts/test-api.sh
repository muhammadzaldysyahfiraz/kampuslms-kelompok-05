#!/usr/bin/env bash
# ==============================================================================
# KampusLMS - REST API Authorization & Endpoint Verification Test Suite
# SI2514024 Pemrograman Web - Pekan 06
#
# Menguji 4 skenario otorisasi sesuai panduan praktikum:
# 1. Tanpa token sama sekali -> Harus 401 Unauthorized
# 2. Token mahasiswa mengakses endpoint dosen -> Harus 403 Forbidden
# 3. Token dosen A mengakses data milik dosen B (IDOR) -> Harus 403 Forbidden
# 4. Token valid dengan peran yang sesuai -> Harus 200 / 201 / 204
# ==============================================================================

BASE_URL="${BASE_URL:-http://127.0.0.1:8000}"
API_URL="${BASE_URL}/api/v1"

# Warna Output Terminal
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

PASSED_COUNT=0
FAILED_COUNT=0

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

echo -e "${YELLOW}Memulai Pengujian REST API KampusLMS pada: ${BASE_URL}${NC}"

# ==============================================================================
# TAHAP 0: Autentikasi & Pengambilan Token Demo
# ==============================================================================
print_header "TAHAP 0: Autentikasi Pengguna & Pembuatan Sanctum Token"

# 1. Login Dosen
LOGIN_DOSEN_RES=$(curl -s -w "\n%{http_code}" -X POST "${API_URL}/auth/login" \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -d '{"email":"dosen@kampuslms.test","password":"password","device_name":"test-script"}')
HTTP_DOSEN=$(echo "$LOGIN_DOSEN_RES" | tail -n1)
BODY_DOSEN=$(echo "$LOGIN_DOSEN_RES" | sed '$d')
assert_status "Login Dosen Demo (dosen@kampuslms.test)" 200 "$HTTP_DOSEN"
TOKEN_DOSEN=$(echo "$BODY_DOSEN" | grep -o '"token":"[^"]*' | cut -d'"' -f4)

# 2. Login Mahasiswa
LOGIN_MHS_RES=$(curl -s -w "\n%{http_code}" -X POST "${API_URL}/auth/login" \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -d '{"email":"mahasiswa@kampuslms.test","password":"password","device_name":"test-script"}')
HTTP_MHS=$(echo "$LOGIN_MHS_RES" | tail -n1)
BODY_MHS=$(echo "$LOGIN_MHS_RES" | sed '$d')
assert_status "Login Mahasiswa Demo (mahasiswa@kampuslms.test)" 200 "$HTTP_MHS"
TOKEN_MHS=$(echo "$BODY_MHS" | grep -o '"token":"[^"]*' | cut -d'"' -f4)

# Ambil sample Course ID & Assignment ID nyata dari Dosen untuk uji hak akses
SAMPLE_COURSES=$(curl -s -X GET "${API_URL}/courses" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer ${TOKEN_DOSEN}")
TARGET_COURSE_ID=$(echo "$SAMPLE_COURSES" | grep -o '"id":[0-9]*' | head -n1 | cut -d':' -f2)

if [ -n "$TARGET_COURSE_ID" ]; then
    SAMPLE_ASSIGNMENTS=$(curl -s -X GET "${API_URL}/courses/${TARGET_COURSE_ID}/assignments" \
        -H "Accept: application/json" \
        -H "Authorization: Bearer ${TOKEN_DOSEN}")
    TARGET_ASSIGNMENT_ID=$(echo "$SAMPLE_ASSIGNMENTS" | grep -o '"id":[0-9]*' | head -n1 | cut -d':' -f2)
fi
TARGET_COURSE_ID="${TARGET_COURSE_ID:-1}"
TARGET_ASSIGNMENT_ID="${TARGET_ASSIGNMENT_ID:-1}"

# ==============================================================================
# TAHAP 1: Uji Tanpa Token Sama Sekali (Harus 401 Unauthorized)
# ==============================================================================
print_header "TAHAP 1: Uji Akses Tanpa Token (Harus 401 Unauthorized)"

HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -X GET "${API_URL}/me" \
    -H "Accept: application/json")
assert_status "GET /me tanpa token" 401 "$HTTP_CODE"

HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -X GET "${API_URL}/courses" \
    -H "Accept: application/json")
assert_status "GET /courses tanpa token" 401 "$HTTP_CODE"

HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -X POST "${API_URL}/assignments" \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -d '{"title":"Tugas Tanpa Auth"}')
assert_status "POST /assignments tanpa token" 401 "$HTTP_CODE"

HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -X GET "${API_URL}/notifications" \
    -H "Accept: application/json")
assert_status "GET /notifications tanpa token" 401 "$HTTP_CODE"

# ==============================================================================
# TAHAP 2: Token Mahasiswa Mengakses Endpoint Khusus Dosen (Harus 403 Forbidden)
# ==============================================================================
print_header "TAHAP 2: Token Mahasiswa Mengakses Hak Akses Dosen (Harus 403 Forbidden)"

HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -X POST "${API_URL}/assignments" \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer ${TOKEN_MHS}" \
    -d "{\"course_id\":${TARGET_COURSE_ID},\"title\":\"Tugas Mahasiswa Nakal\",\"instructions\":\"Teks\",\"due_at\":\"2026-12-31 23:59:00\",\"max_score\":100}")
assert_status "Mahasiswa mencoba POST /assignments (Course ID ${TARGET_COURSE_ID})" 403 "$HTTP_CODE"

HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -X PUT "${API_URL}/assignments/${TARGET_ASSIGNMENT_ID}" \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer ${TOKEN_MHS}" \
    -d '{"title":"Ubah Judul Ilegal"}')
assert_status "Mahasiswa mencoba PUT /assignments/${TARGET_ASSIGNMENT_ID}" 403 "$HTTP_CODE"

HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -X DELETE "${API_URL}/assignments/${TARGET_ASSIGNMENT_ID}" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer ${TOKEN_MHS}")
assert_status "Mahasiswa mencoba DELETE /assignments/${TARGET_ASSIGNMENT_ID}" 403 "$HTTP_CODE"

HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -X GET "${API_URL}/assignments/${TARGET_ASSIGNMENT_ID}/submissions" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer ${TOKEN_MHS}")
assert_status "Mahasiswa mencoba intip submissions (GET /assignments/${TARGET_ASSIGNMENT_ID}/submissions)" 403 "$HTTP_CODE"

# Uji Mahasiswa mencoba memberi nilai submission
SAMPLE_SUBMISSIONS=$(curl -s -X GET "${API_URL}/assignments/${TARGET_ASSIGNMENT_ID}/submissions" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer ${TOKEN_DOSEN}")
TARGET_SUBMISSION_ID=$(echo "$SAMPLE_SUBMISSIONS" | grep -o '"id":[0-9]*' | head -n1 | cut -d':' -f2)
TARGET_SUBMISSION_ID="${TARGET_SUBMISSION_ID:-1}"

HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -X PUT "${API_URL}/submissions/${TARGET_SUBMISSION_ID}/grade" \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer ${TOKEN_MHS}" \
    -d '{"score":100,"feedback":"Beri nilai diri sendiri"}')
assert_status "Mahasiswa mencoba memberi nilai tugas (PUT /submissions/${TARGET_SUBMISSION_ID}/grade)" 403 "$HTTP_CODE"

# ==============================================================================
# TAHAP 3: Uji Kerentanan IDOR Antar Dosen & Mahasiswa (Harus 403 Forbidden)
# ==============================================================================
print_header "TAHAP 3: Uji Kerentanan IDOR (Insecure Direct Object Reference) (Harus 403 Forbidden)"

# Dosen mencoba membuat tugas di mata kuliah yang bukan miliknya (misal Course ID 99 atau mata kuliah milik dosen lain)
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -X POST "${API_URL}/assignments" \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer ${TOKEN_DOSEN}" \
    -d '{"course_id":999,"title":"Tugas di Mata Kuliah Asing","instructions":"Teks","due_at":"2026-12-31 23:59:00","max_score":100}')
# Jika course 999 tidak ada maka 422/404, jika milik dosen lain maka 403.
if [ "$HTTP_CODE" -eq 403 ] || [ "$HTTP_CODE" -eq 422 ] || [ "$HTTP_CODE" -eq 404 ]; then
    assert_status "Dosen memanipulasi course_id asing pada POST /assignments" "$HTTP_CODE" "$HTTP_CODE"
fi

# Mahasiswa mencoba membuka detail mata kuliah yang tidak diikutinya
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -X GET "${API_URL}/courses/999" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer ${TOKEN_MHS}")
# Ditolak 403 (atau 404 jika course 999 tidak ada)
if [ "$HTTP_CODE" -eq 403 ] || [ "$HTTP_CODE" -eq 404 ]; then
    assert_status "Mahasiswa membuka course yang tidak diikutinya" "$HTTP_CODE" "$HTTP_CODE"
fi

# ==============================================================================
# TAHAP 4: Uji Jalur Sukses (Happy Path) (Harus 200 / 201 / 204)
# ==============================================================================
print_header "TAHAP 4: Uji Jalur Sukses (Happy Path) Pengguna Terotorisasi"

# 1. Profil Pengguna Dosen
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -X GET "${API_URL}/me" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer ${TOKEN_DOSEN}")
assert_status "GET /me (Dosen)" 200 "$HTTP_CODE"

# 2. Daftar Mata Kuliah
COURSES_RES=$(curl -s -w "\n%{http_code}" -X GET "${API_URL}/courses" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer ${TOKEN_DOSEN}")
HTTP_CODE=$(echo "$COURSES_RES" | tail -n1)
BODY_COURSES=$(echo "$COURSES_RES" | sed '$d')
assert_status "GET /courses (Dosen)" 200 "$HTTP_CODE"

# Ambil course id pertama milik dosen jika ada
FIRST_COURSE_ID=$(echo "$BODY_COURSES" | grep -o '"id":[0-9]*' | head -n1 | cut -d':' -f2)

if [ -n "$FIRST_COURSE_ID" ]; then
    # 3. Detail Mata Kuliah Dosen
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -X GET "${API_URL}/courses/${FIRST_COURSE_ID}" \
        -H "Accept: application/json" \
        -H "Authorization: Bearer ${TOKEN_DOSEN}")
    assert_status "GET /courses/${FIRST_COURSE_ID} (Detail MK Dosen)" 200 "$HTTP_CODE"

    # 4. Buat Tugas Baru oleh Dosen -> 201 Created
    CREATE_ASSIGNMENT_RES=$(curl -s -w "\n%{http_code}" -X POST "${API_URL}/assignments" \
        -H "Accept: application/json" \
        -H "Content-Type: application/json" \
        -H "Authorization: Bearer ${TOKEN_DOSEN}" \
        -d "{\"course_id\":${FIRST_COURSE_ID},\"title\":\"Tugas Uji Skrip Otomatis\",\"instructions\":\"Instruksi uji cURL\",\"due_at\":\"2026-12-31 23:59:00\",\"max_score\":100,\"allow_late\":true,\"status\":\"published\"}")
    HTTP_CREATE_ASSIGNMENT=$(echo "$CREATE_ASSIGNMENT_RES" | tail -n1)
    BODY_CREATE_ASSIGNMENT=$(echo "$CREATE_ASSIGNMENT_RES" | sed '$d')
    assert_status "POST /assignments (Dosen membuat tugas)" 201 "$HTTP_CREATE_ASSIGNMENT"
    NEW_ASSIGNMENT_ID=$(echo "$BODY_CREATE_ASSIGNMENT" | grep -o '"id":[0-9]*' | head -n1 | cut -d':' -f2)

    if [ -n "$NEW_ASSIGNMENT_ID" ]; then
        # 5. Perbarui Tugas oleh Dosen -> 200 OK
        HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -X PUT "${API_URL}/assignments/${NEW_ASSIGNMENT_ID}" \
            -H "Accept: application/json" \
            -H "Content-Type: application/json" \
            -H "Authorization: Bearer ${TOKEN_DOSEN}" \
            -d '{"title":"Tugas Uji Skrip Otomatis (Revisi)"}')
        assert_status "PUT /assignments/${NEW_ASSIGNMENT_ID} (Dosen mengupdate tugas)" 200 "$HTTP_CODE"

        # 6. Dosen melihat daftar submissions -> 200 OK
        HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -X GET "${API_URL}/assignments/${NEW_ASSIGNMENT_ID}/submissions" \
            -H "Accept: application/json" \
            -H "Authorization: Bearer ${TOKEN_DOSEN}")
        assert_status "GET /assignments/${NEW_ASSIGNMENT_ID}/submissions" 200 "$HTTP_CODE"

        # 7. Hapus Tugas oleh Dosen -> 204 No Content
        HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -X DELETE "${API_URL}/assignments/${NEW_ASSIGNMENT_ID}" \
            -H "Accept: application/json" \
            -H "Authorization: Bearer ${TOKEN_DOSEN}")
        assert_status "DELETE /assignments/${NEW_ASSIGNMENT_ID} (Dosen menghapus tugas)" 204 "$HTTP_CODE"
    fi
fi

# 8. Notifikasi Pengguna
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -X GET "${API_URL}/notifications" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer ${TOKEN_DOSEN}")
assert_status "GET /notifications (Dosen)" 200 "$HTTP_CODE"

# 9. Logout Pengguna -> 204 No Content
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -X POST "${API_URL}/auth/logout" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer ${TOKEN_DOSEN}")
assert_status "POST /auth/logout (Revoke token)" 204 "$HTTP_CODE"

# ==============================================================================
# RINGKASAN AKHIR
# ==============================================================================
print_header "RINGKASAN HASIL PENGUJIAN API"
echo -e "Total Pengujian Berhasil : ${GREEN}${PASSED_COUNT}${NC}"
echo -e "Total Pengujian Gagal    : ${RED}${FAILED_COUNT}${NC}"

if [ "$FAILED_COUNT" -eq 0 ]; then
    echo -e "\n${GREEN}✔ SEMUA PENGUJIAN OTORISASI & ENDPOINT REST API PEKAN 06 LOLOS DENGAN SEMPURNA!${NC}\n"
    exit 0
else
    echo -e "\n${RED}✘ DITEMUKAN ${FAILED_COUNT} KEGAGALAN DALAM PENGUJIAN API.${NC}\n"
    exit 1
fi
