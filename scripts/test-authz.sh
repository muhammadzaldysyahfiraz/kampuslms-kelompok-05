#!/usr/bin/env bash
# ==============================================================================
# KampusLMS - Production-capable Web and API authorization verification suite
# ==============================================================================

BASE_URL="${BASE_URL:-http://127.0.0.1:8000}"
while [[ "$BASE_URL" == */ ]]; do
    BASE_URL="${BASE_URL%/}"
done
API_URL="${BASE_URL}/api/v1"
MHS_EMAIL="${MHS_EMAIL:-mahasiswa@kampuslms.test}"
MHS_PASSWORD="${MHS_PASSWORD:-password}"
DOSEN_EMAIL="${DOSEN_EMAIL:-dosen@kampuslms.test}"
DOSEN_PASSWORD="${DOSEN_PASSWORD:-password}"
OWN_SUBM_ID="${OWN_SUBM_ID:-1}"
OTHER_SUBM_ID="${OTHER_SUBM_ID:-79}"
OWN_ASSIGN_ID="${OWN_ASSIGN_ID:-4}"
OTHER_ASSIGN_ID="${OTHER_ASSIGN_ID:-7}"
EXPECT_PROD="${EXPECT_PROD:-0}"

COOKIE_JAR_MHS=$(mktemp)
COOKIE_JAR_DOSEN=$(mktemp)
LOGIN_PAGE_MHS=$(mktemp)
LOGIN_PAGE_DOSEN=$(mktemp)

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

PASSED_COUNT=0
FAILED_COUNT=0

cleanup() {
    rm -f "$COOKIE_JAR_MHS" "$COOKIE_JAR_DOSEN" "$LOGIN_PAGE_MHS" "$LOGIN_PAGE_DOSEN"
}
trap cleanup EXIT

json_escape() {
    local value="$1"
    value=${value//\\/\\\\}
    value=${value//\"/\\\"}
    value=${value//$'\n'/\\n}
    value=${value//$'\r'/\\r}
    value=${value//$'\t'/\\t}
    printf '%s' "$value"
}

extract_csrf() {
    sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' "$1" | head -n 1
}

print_header() {
    printf '\n%b==============================================================================%b\n' "$BLUE" "$NC"
    printf '%b  %s%b\n' "$CYAN" "$1" "$NC"
    printf '%b==============================================================================%b\n' "$BLUE" "$NC"
}

assert_status() {
    local description="$1"
    local expected="$2"
    local actual="$3"

    if [[ "$actual" =~ ^[0-9]{3}$ ]] && [ "$actual" -eq "$expected" ]; then
        printf '  [%bPASS%b] %s (HTTP %s)\n' "$GREEN" "$NC" "$description" "$actual"
        PASSED_COUNT=$((PASSED_COUNT + 1))
    else
        printf '  [%bFAIL%b] %s (expected HTTP %s, received HTTP %s)\n' "$RED" "$NC" "$description" "$expected" "$actual"
        FAILED_COUNT=$((FAILED_COUNT + 1))
    fi
}

assert_not_status() {
    local description="$1"
    local forbidden="$2"
    local actual="$3"

    if [[ "$actual" =~ ^[0-9]{3}$ ]] && [ "$actual" -ne "$forbidden" ]; then
        printf '  [%bPASS%b] %s (HTTP %s)\n' "$GREEN" "$NC" "$description" "$actual"
        PASSED_COUNT=$((PASSED_COUNT + 1))
    else
        printf '  [%bFAIL%b] %s (HTTP %s is forbidden)\n' "$RED" "$NC" "$description" "$actual"
        FAILED_COUNT=$((FAILED_COUNT + 1))
    fi
}

assert_success_status() {
    local description="$1"
    local actual="$2"

    if [[ "$actual" == "200" || "$actual" == "302" ]]; then
        printf '  [%bPASS%b] %s (HTTP %s)\n' "$GREEN" "$NC" "$description" "$actual"
        PASSED_COUNT=$((PASSED_COUNT + 1))
    else
        printf '  [%bFAIL%b] %s (expected HTTP 200 or 302, received HTTP %s)\n' "$RED" "$NC" "$description" "$actual"
        FAILED_COUNT=$((FAILED_COUNT + 1))
    fi
}

login_web() {
    local email="$1"
    local password="$2"
    local cookie_jar="$3"
    local response_file="$4"
    local login_token

    curl -sS -c "$cookie_jar" -b "$cookie_jar" -o "$response_file" "${BASE_URL}/login"
    login_token=$(extract_csrf "$response_file")
    if [ -z "$login_token" ]; then
        printf 'Unable to extract the login CSRF token for %s.\n' "$email" >&2
        return 1
    fi

    curl -sS -L --max-redirs 5 -b "$cookie_jar" -c "$cookie_jar" \
        -o "$response_file" -w '%{http_code}' \
        --data-urlencode "_token=${login_token}" \
        --data-urlencode "email=${email}" \
        --data-urlencode "password=${password}" \
        "${BASE_URL}/login"
}

printf '%bRunning authorization checks against: %s%b\n' "$YELLOW" "$BASE_URL" "$NC"

print_header "STAGE 1: Web login and Sanctum token setup"
MHS_LOGIN_STATUS=$(login_web "$MHS_EMAIL" "$MHS_PASSWORD" "$COOKIE_JAR_MHS" "$LOGIN_PAGE_MHS")
DOSEN_LOGIN_STATUS=$(login_web "$DOSEN_EMAIL" "$DOSEN_PASSWORD" "$COOKIE_JAR_DOSEN" "$LOGIN_PAGE_DOSEN")
assert_status "Mahasiswa web login" 200 "$MHS_LOGIN_STATUS"
assert_status "Dosen web login" 200 "$DOSEN_LOGIN_STATUS"

MHS_CSRF=$(extract_csrf "$LOGIN_PAGE_MHS")
DOSEN_CSRF=$(extract_csrf "$LOGIN_PAGE_DOSEN")
if [ -z "$MHS_CSRF" ]; then
    printf '%bUnable to extract the mahasiswa CSRF token from the post-login page.%b\n' "$RED" "$NC" >&2
    exit 1
fi
if [ -z "$DOSEN_CSRF" ]; then
    printf '%bUnable to extract the dosen CSRF token from the post-login page.%b\n' "$RED" "$NC" >&2
    exit 1
fi

MHS_EMAIL_JSON=$(json_escape "$MHS_EMAIL")
MHS_PASSWORD_JSON=$(json_escape "$MHS_PASSWORD")
TOKEN_RESPONSE=$(curl -sS -X POST "${API_URL}/auth/login" \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -d "{\"email\":\"${MHS_EMAIL_JSON}\",\"password\":\"${MHS_PASSWORD_JSON}\"}")
TOKEN_MHS=$(printf '%s' "$TOKEN_RESPONSE" | grep -o '"token":"[^"]*' | cut -d'"' -f4)
if [ -z "$TOKEN_MHS" ]; then
    printf '%bUnable to obtain a Sanctum token for the mahasiswa account.%b\n' "$RED" "$NC" >&2
fi

print_header "STAGE 2: Submission authorization and downloads"
CODE_SUBM_OTHER=$(curl -sS -b "$COOKIE_JAR_MHS" -o /dev/null -w '%{http_code}' "${BASE_URL}/submissions/${OTHER_SUBM_ID}")
assert_status "Mahasiswa cannot view another student's submission" 403 "$CODE_SUBM_OTHER"

CODE_SUBM_OWN=$(curl -sS -b "$COOKIE_JAR_MHS" -o /dev/null -w '%{http_code}' "${BASE_URL}/submissions/${OWN_SUBM_ID}")
assert_status "Mahasiswa can view their own submission" 200 "$CODE_SUBM_OWN"

CODE_DOWNLOAD_GUEST=$(curl -sS -o /dev/null -w '%{http_code}' "${BASE_URL}/submissions/${OWN_SUBM_ID}/download")
assert_not_status "Guest cannot download a submission" 200 "$CODE_DOWNLOAD_GUEST"

CODE_DOWNLOAD_OTHER=$(curl -sS -b "$COOKIE_JAR_MHS" -o /dev/null -w '%{http_code}' "${BASE_URL}/submissions/${OTHER_SUBM_ID}/download")
assert_status "Mahasiswa cannot download another student's submission" 403 "$CODE_DOWNLOAD_OTHER"

CODE_GRADE_MHS=$(curl -sS -b "$COOKIE_JAR_MHS" -o /dev/null -w '%{http_code}' -X PUT \
    "${API_URL}/submissions/${OWN_SUBM_ID}/grade" \
    -H "Authorization: Bearer ${TOKEN_MHS}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"score":100,"feedback":"Authorization test"}')
assert_status "Mahasiswa cannot grade a submission through the API" 403 "$CODE_GRADE_MHS"

print_header "STAGE 3: Cross-lecturer assignment authorization"
CODE_ASSIGN_OTHER=$(curl -sS -b "$COOKIE_JAR_DOSEN" -o /dev/null -w '%{http_code}' -X PUT \
    "${BASE_URL}/assignments/${OTHER_ASSIGN_ID}" \
    -H "X-CSRF-TOKEN: ${DOSEN_CSRF}" \
    -H "Accept: application/json" \
    --data-urlencode "title=Bukti uji otorisasi")
assert_status "Dosen cannot update another lecturer's assignment" 403 "$CODE_ASSIGN_OTHER"

CODE_ASSIGN_OWN=$(curl -sS -b "$COOKIE_JAR_DOSEN" -o /dev/null -w '%{http_code}' -X PUT \
    "${BASE_URL}/assignments/${OWN_ASSIGN_ID}" \
    -H "X-CSRF-TOKEN: ${DOSEN_CSRF}" \
    -H "Accept: application/json" \
    --data-urlencode "title=Tugas 1: Pemodelan Data Relasional & Perancangan Skema ERD")
assert_success_status "Dosen can update their own assignment" "$CODE_ASSIGN_OWN"

print_header "STAGE 4: Scoped route binding"
CODE_SCOPED_ASSIGN=$(curl -sS -b "$COOKIE_JAR_DOSEN" -o /dev/null -w '%{http_code}' \
    "${BASE_URL}/courses/2/assignments/1")
assert_status "Cross-course assignment route binding returns 404" 404 "$CODE_SCOPED_ASSIGN"

CODE_SCOPED_MATERIAL=$(curl -sS -b "$COOKIE_JAR_DOSEN" -o /dev/null -w '%{http_code}' \
    "${BASE_URL}/courses/2/materials/1")
assert_status "Cross-course material route binding returns 404" 404 "$CODE_SCOPED_MATERIAL"

print_header "STAGE 5: User management RBAC"
CODE_USERS_MHS=$(curl -sS -b "$COOKIE_JAR_MHS" -o /dev/null -w '%{http_code}' "${BASE_URL}/users")
assert_status "Mahasiswa cannot access /users" 403 "$CODE_USERS_MHS"

CODE_USERS_DOSEN=$(curl -sS -b "$COOKIE_JAR_DOSEN" -o /dev/null -w '%{http_code}' "${BASE_URL}/users")
assert_status "Dosen cannot access /users" 403 "$CODE_USERS_DOSEN"

if [[ "$EXPECT_PROD" == "1" || "$BASE_URL" == https://* ]]; then
    print_header "STAGE 6: Production exposure checks"
    for path in '/.env' '/storage/logs/laravel.log' '/composer.json'; do
        CODE_SENSITIVE=$(curl -sS -o /dev/null -w '%{http_code}' "${BASE_URL}${path}")
        assert_not_status "Sensitive path ${path} is not publicly accessible" 200 "$CODE_SENSITIVE"
    done

    CODE_SWITCH_ROLE=$(curl -sS -o /dev/null -w '%{http_code}' "${BASE_URL}/switch-role/admin")
    assert_status "Production switch-role route is absent" 404 "$CODE_SWITCH_ROLE"
fi

printf '\n%b==============================================================================%b\n' "$BLUE" "$NC"
printf '  Authorization results: %b%s passed%b, %b%s failed%b\n' "$GREEN" "$PASSED_COUNT" "$NC" "$RED" "$FAILED_COUNT" "$NC"
printf '%b==============================================================================%b\n' "$BLUE" "$NC"

if [ "$FAILED_COUNT" -eq 0 ]; then
    printf '%bAll authorization checks passed.%b\n\n' "$GREEN" "$NC"
    exit 0
fi

printf '%bOne or more authorization checks failed.%b\n\n' "$RED" "$NC"
exit 1
