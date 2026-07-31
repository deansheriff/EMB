#!/bin/sh
set -eu

mkdir -p /var/www/html/public/uploads /var/www/html/storage/logs /var/www/html/storage/grant-documents
chown -R www-data:www-data /var/www/html/public/uploads /var/www/html/storage

mysql_command() {
  MYSQL_PWD="${DB_PASSWORD:-}" mysql \
    --protocol=TCP \
    --host="${DB_HOST}" \
    --port="${DB_PORT:-3306}" \
    --user="${DB_USERNAME}" \
    --default-character-set=utf8mb4 \
    "${DB_DATABASE}" "$@"
}

run_sql_file() {
  source_file="$1"
  cleaned_file="/tmp/$(basename "${source_file}")"
  sed '/^[[:space:]]*USE[[:space:]]/Id' "${source_file}" > "${cleaned_file}"
  mysql_command < "${cleaned_file}"
  rm -f "${cleaned_file}"
}

table_exists() {
  table_name="$1"
  result="$(mysql_command --batch --skip-column-names --execute="SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = '${table_name}'")"
  [ "${result}" = "1" ]
}

if [ "${DB_BOOTSTRAP:-true}" = "true" ]; then
  : "${DB_HOST:?DB_HOST is required when DB_BOOTSTRAP=true}"
  : "${DB_DATABASE:?DB_DATABASE is required when DB_BOOTSTRAP=true}"
  : "${DB_USERNAME:?DB_USERNAME is required when DB_BOOTSTRAP=true}"

  elapsed=0
  timeout="${DB_WAIT_TIMEOUT:-60}"
  until mysql_command --execute="SELECT 1" >/dev/null 2>&1; do
    if [ "${elapsed}" -ge "${timeout}" ]; then
      echo "Database did not become ready within ${timeout} seconds." >&2
      exit 1
    fi
    elapsed=$((elapsed + 2))
    sleep 2
  done

  if ! table_exists "admins"; then
    echo "Initializing the EMB Chronicles database."
    schema_file="/tmp/emb-schema.sql"
    awk 'found { print } /^[[:space:]]*USE[[:space:]]+emb_chronicles;/ { found=1 }' /var/www/html/database/schema.sql > "${schema_file}"
    mysql_command < "${schema_file}"
    rm -f "${schema_file}"
    run_sql_file /var/www/html/database/seed.sql
  fi

  if ! table_exists "appointment_payments"; then
    echo "Applying email and appointment-payment migration."
    run_sql_file /var/www/html/database/migrations/20260731_email_payments.sql
  fi

  if ! table_exists "roles"; then
    echo "Applying role and access-control migration."
    run_sql_file /var/www/html/database/migrations/20260731_rbac.sql
  fi

  grant_form_count=0
  if table_exists "grant_forms"; then
    grant_form_count="$(mysql_command --batch --skip-column-names --execute="SELECT COUNT(*) FROM grant_forms WHERE slug = 'fiyff-fertility-support-grant'")"
  fi
  if [ "${grant_form_count}" = "0" ]; then
    echo "Applying managed grant-form migration."
    run_sql_file /var/www/html/database/migrations/20260731_grant_forms.sql
  fi
fi

exec "$@"
