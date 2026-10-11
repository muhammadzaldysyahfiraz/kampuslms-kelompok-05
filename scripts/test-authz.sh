#!/usr/bin/env bash
set -euo pipefail

# Set these using disposable development accounts and records.
: "${BASE_URL:?Set BASE_URL, e.g. http://kampuslms.test}"
: "${LECTURER_A_TOKEN:?Set token for lecturer A}"
: "${STUDENT_TOKEN:?Set token for a student}"
: "${OTHER_COURSE_ID:?Set a course owned by a different lecturer}"
: "${OTHER_ASSIGNMENT_ID:?Set an assignment owned by a different lecturer}"
: "${LECTURER_ASSIGNMENT_ID:?Set an assignment belonging to lecturer A}"

expect_denied() {
    local label="$1" token="$2" method="$3" path="$4"
    local code
    code="$(curl --silent --output /dev/null --write-out '%{http_code}' \
        --request "$method" \
        --header "Authorization: Bearer $token" \
        --header 'Accept: application/json' \
        "${BASE_URL%/}${path}")"
    case "$code" in
        403|404) printf 'PASS %s: HTTP %s\n' "$label" "$code" ;;
        *) printf 'FAIL %s: expected 403/404, got HTTP %s\n' "$label" "$code" >&2; exit 1 ;;
    esac
}

expect_denied 'lecturer cannot read another course' "$LECTURER_A_TOKEN" GET "/api/v1/courses/$OTHER_COURSE_ID"
expect_denied 'lecturer cannot update another lecturer assignment' "$LECTURER_A_TOKEN" PUT "/api/v1/assignments/$OTHER_ASSIGNMENT_ID"
expect_denied 'student cannot list submissions for lecturer assignment' "$STUDENT_TOKEN" GET "/api/v1/assignments/$LECTURER_ASSIGNMENT_ID/submissions"
