#!/bin/bash
{
set -e

source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/scripts/lib.sh"

BG_PIDS=()

cleanup() {
    echo ""
    echo -e "${RED}Interruption detectee, nettoyage...${NC}"
    stop_spinner
    for pid in "${BG_PIDS[@]}"; do
        kill "$pid" 2>/dev/null
    done
    wait 2>/dev/null
    printf "\r"
    echo -e "${RED}✗ Initialisation annulee${NC}"
    exit 130
}
trap cleanup INT TERM

# ─── Prompt projet ───────────────────────────────────────────────────

prompt_project_info() {
    DEFAULT_NAME="$(basename "$PROJECT_ROOT")"
    DEFAULT_NAME="${DEFAULT_NAME%.git}"
    DEFAULT_NAME="$(echo "$DEFAULT_NAME" | tr ' ' '-')"

    if [ -n "$1" ]; then
        PROJECT_NAME="$1"
    else
        read -rp "Nom du projet [${DEFAULT_NAME}]: " PROJECT_NAME
        if [ -z "$PROJECT_NAME" ]; then
            PROJECT_NAME="$DEFAULT_NAME"
            echo -e "${YELLOW}Utilisation du nom du dossier: ${PROJECT_NAME}${NC}"
        fi
    fi

    if ! [[ "$PROJECT_NAME" =~ ^[a-zA-Z0-9_-]+$ ]]; then
        echo -e "${RED}Erreur: Le nom du projet ne peut contenir que des lettres, chiffres, tirets et underscores${NC}"
        exit 1
    fi

    CURRENT_GIT_URL=$(git -C "$PROJECT_ROOT" remote get-url origin 2>/dev/null || echo "")
    read -rp "URL du depot Git [${CURRENT_GIT_URL:-aucune}]: " NEW_GIT_URL

    read -rp "Multilangue (y/n) [n]: " MULTILANG_CHOICE
    MULTILANG_CHOICE="${MULTILANG_CHOICE:-n}"
    if [[ "$MULTILANG_CHOICE" =~ ^[yY]$ ]]; then
        MULTILANG=true
    else
        MULTILANG=false
    fi
}

# ─── Git remote ──────────────────────────────────────────────────────

configure_git_remote() {
    if [ -n "$NEW_GIT_URL" ]; then
        CURRENT_GIT_URL=$(git -C "$PROJECT_ROOT" remote get-url origin 2>/dev/null || echo "")
        start_spinner "Configuration du remote origin..."
        if [ -z "$CURRENT_GIT_URL" ]; then
            git -C "$PROJECT_ROOT" remote add origin "$NEW_GIT_URL"
        else
            git -C "$PROJECT_ROOT" remote set-url origin "$NEW_GIT_URL"
        fi
        stop_spinner "ok" "Remote origin mis a jour: ${NEW_GIT_URL}"
    else
        echo -e "  ${YELLOW}⚠ Remote origin inchange: ${CURRENT_GIT_URL:-non defini}${NC}"
    fi
}

# ─── Fichier .env ────────────────────────────────────────────────────

configure_env_file() {
    local env_sample="$DOCKER_DIR/.env-sample"

    if [ ! -f "$env_sample" ]; then
        echo -e "${RED}Erreur: Fichier .env-sample non trouve dans $DOCKER_DIR${NC}"
        exit 1
    fi

    start_spinner "Configuration du fichier .env..."
    [ -f "$ENV_FILE" ] && rm "$ENV_FILE"
    cp "$env_sample" "$ENV_FILE"

    portable_sed "s/^PROJECT_NAME=.*/PROJECT_NAME=${PROJECT_NAME}/" "$ENV_FILE"

    find_global_env || true
    if [ -n "$GLOBAL_ENV" ]; then
        [ -n "$(tail -c1 "$ENV_FILE")" ] && printf '\n' >> "$ENV_FILE"
        for KEY in DB_HOST DB_USER DB_ROOT_PASSWORD DB_PASSWORD; do
            GLOBAL_VAL=$(global_env_get "$KEY")
            if [ -n "$GLOBAL_VAL" ]; then
                portable_sed "s|^${KEY}=.*|${KEY}=${GLOBAL_VAL}|" "$ENV_FILE"
            fi
        done
        for KEY in ACF_PRO_LICENSE WPML_USER_ID WPML_SUBSCRIPTION_KEY; do
            LOCAL_VAL=$(grep "^${KEY}=" "$ENV_FILE" | cut -d'=' -f2-)
            if [ -z "$LOCAL_VAL" ]; then
                GLOBAL_LINE=$(grep "^${KEY}=" "$GLOBAL_ENV")
                [ -n "$GLOBAL_LINE" ] && printf '%s\n' "$GLOBAL_LINE" >> "$ENV_FILE"
            fi
        done
    fi

    stop_spinner "ok" "Fichier .env configure (PROJECT_NAME=${PROJECT_NAME})"
}

# ─── Docker compose sample ──────────────────────────────────────────

copy_compose_sample() {
    local compose_sample="$DOCKER_DIR/docker-compose-sample.yml"
    if [ ! -f "$COMPOSE_FILE" ] && [ -f "$compose_sample" ]; then
        start_spinner "Creation du fichier docker-compose.yml..."
        cp "$compose_sample" "$COMPOSE_FILE"
        stop_spinner "ok" "docker-compose.yml cree depuis docker-compose-sample.yml"
    fi
}

# ─── Renommage du theme ─────────────────────────────────────────────

rename_theme() {
    local theme_dir="$PROJECT_ROOT/web/wp-content/themes"
    NEW_THEME_PATH="$theme_dir/$PROJECT_NAME"

    local existing_themes
    existing_themes=($(find "$theme_dir" -mindepth 1 -maxdepth 1 -type d))

    if [ ${#existing_themes[@]} -eq 0 ]; then
        echo -e "${RED}Erreur: Aucun dossier theme trouve dans $theme_dir${NC}"
        exit 1
    elif [ ${#existing_themes[@]} -gt 1 ]; then
        echo -e "${RED}Erreur: Plusieurs dossiers themes trouves dans $theme_dir. Un seul attendu.${NC}"
        exit 1
    fi

    local old_path="${existing_themes[0]}"
    local old_name
    old_name=$(basename "$old_path")

    if [ "$old_name" = "$PROJECT_NAME" ]; then
        echo -e "  ${YELLOW}⚠ Le theme est deja nomme ${PROJECT_NAME}${NC}"
    else
        start_spinner "Renommage du theme..."
        mv "$old_path" "$NEW_THEME_PATH"
        stop_spinner "ok" "Dossier ${old_name} renomme en ${PROJECT_NAME}"
    fi
}

# ─── Sonar properties ───────────────────────────────────────────────

update_sonar_props() {
    local sonar_props="$DOCKER_DIR/sonarqube/sonar-project.properties"
    if [ -f "$sonar_props" ]; then
        start_spinner "Mise a jour de sonar-project.properties..."
        portable_sed "s|themes/[^,]*|themes/${PROJECT_NAME}|g" "$sonar_props"
        stop_spinner "ok" "sonar-project.properties mis a jour (themes/${PROJECT_NAME})"
    fi
}

# ─── Infra locale (hosts + SSL + BDD en parallele) ──────────────────

setup_local_infra() {
    sudo -v 2>/dev/null
    start_spinner "Configuration locale (hosts, SSL, base de donnees)..."

    "$DOCKER_DIR/scripts/hosts-file-setup.sh" &>/dev/null &
    local pid_hosts=$!
    BG_PIDS+=($pid_hosts)

    local cert_dir="$DOCKER_DIR/certs"
    local domain="${PROJECT_NAME}.code"
    local cert_skipped=0 pid_cert=""

    if [ -f "$cert_dir/${domain}.crt" ] && [ -f "$cert_dir/${domain}.key" ]; then
        cert_skipped=1
    else
        rm -f "$cert_dir"/*.crt "$cert_dir"/*.key
        ( cd "$PROJECT_ROOT" && "$DOCKER_DIR/scripts/cert-create.sh" && "$DOCKER_DIR/scripts/cert-trust.sh" ) &>/dev/null &
        pid_cert=$!
        BG_PIDS+=($pid_cert)
    fi

    local db_host db_name db_user db_password db_port=""
    db_host=$(grep '^DB_HOST=' "$ENV_FILE" | cut -d'=' -f2)
    db_name=$(grep '^DB_NAME=' "$ENV_FILE" | cut -d'=' -f2)
    db_user=$(grep '^DB_USER=' "$ENV_FILE" | cut -d'=' -f2)
    db_password=$(grep '^DB_PASSWORD=' "$ENV_FILE" | cut -d'=' -f2)
    db_name="${db_name//\$\{PROJECT_NAME\}/$PROJECT_NAME}"
    db_host="${db_host/host.docker.internal/localhost}"

    if [[ "$db_host" == *:* ]]; then
        db_port="${db_host##*:}"
        db_host="${db_host%%:*}"
    fi

    local db_cmd="" mamp_socket=""
    if command -v mariadb &>/dev/null; then
        db_cmd="mariadb"
    elif command -v mysql &>/dev/null; then
        db_cmd="mysql"
    elif [ -x "/Applications/MAMP/Library/bin/mysql80/bin/mysql" ]; then
        db_cmd="/Applications/MAMP/Library/bin/mysql80/bin/mysql"
        mamp_socket="/Applications/MAMP/tmp/mysql/mysql.sock"
    elif [ -x "/Applications/MAMP/Library/bin/mysql57/bin/mysql" ]; then
        db_cmd="/Applications/MAMP/Library/bin/mysql57/bin/mysql"
        mamp_socket="/Applications/MAMP/tmp/mysql/mysql.sock"
    fi

    local pid_db="" db_err_file=""
    if [ -n "$db_cmd" ]; then
        local sql="CREATE DATABASE IF NOT EXISTS \`${db_name}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
        local db_extra_args=""
        [ -n "$mamp_socket" ] && db_extra_args="--socket=$mamp_socket"
        [ -n "$db_port" ] && db_extra_args="$db_extra_args --port=$db_port"
        db_err_file=$(mktemp)
        ( $db_cmd -h "$db_host" -u "$db_user" -p"$db_password" $db_extra_args -e "$sql" 2>"$db_err_file" || \
          $db_cmd -h "$db_host" -u "$db_user" $db_extra_args -e "$sql" 2>"$db_err_file" ) &
        pid_db=$!
        BG_PIDS+=($pid_db)
    fi

    local hosts_ok=true cert_ok=true db_ok=true
    wait $pid_hosts 2>/dev/null || hosts_ok=false
    [ -n "$pid_cert" ] && { wait $pid_cert 2>/dev/null || cert_ok=false; }
    [ -n "$pid_db" ] && { wait $pid_db 2>/dev/null || db_ok=false; }

    stop_spinner "ok" "Configuration locale terminee"

    if $hosts_ok; then
        echo -e "  ${GREEN}✓ Fichier hosts configure${NC}"
    else
        echo -e "  ${RED}✗ Configuration hosts echouee${NC}"
    fi

    if [ "$cert_skipped" = "1" ]; then
        echo -e "  ${YELLOW}⚠ Certificat SSL deja present, generation ignoree${NC}"
    elif $cert_ok; then
        echo -e "  ${GREEN}✓ Certificat SSL cree et approuve${NC}"
    else
        echo -e "  ${RED}✗ Erreur certificat SSL${NC}"
    fi

    if [ -z "$db_cmd" ]; then
        echo -e "  ${RED}✗ MariaDB/MySQL n'est pas installe ou n'est pas dans le PATH (MAMP non detecte)${NC}"
    elif $db_ok; then
        echo -e "  ${GREEN}✓ Base de donnees '${db_name}' creee (${db_user}@${db_host})${NC}"
    else
        local db_display_host="$db_host"
        [ -n "$db_port" ] && db_display_host="${db_host}:${db_port}"
        echo -e "  ${RED}✗ Impossible de creer la base de donnees (Host: ${db_display_host} | User: ${db_user} | DB: ${db_name})${NC}"
        [ -f "$db_err_file" ] && [ -s "$db_err_file" ] && echo -e "  ${RED}  → $(cat "$db_err_file")${NC}"
    fi

    DB_NAME="$db_name"
}

# ─── Theme Name dans style.css ───────────────────────────────────────

update_theme_name() {
    local theme_dir="$PROJECT_ROOT/web/wp-content/themes"
    local style_css="$theme_dir/$PROJECT_NAME/style.css"
    if [ -f "$style_css" ]; then
        start_spinner "Mise a jour du Theme Name..."
        THEME_DISPLAY_NAME=$(echo "$PROJECT_NAME" | tr '_-' '  ' | tr '[:lower:]' '[:upper:]')
        portable_sed "s/^Theme Name: .*/Theme Name: ${THEME_DISPLAY_NAME}/" "$style_css"
        stop_spinner "ok" "Theme Name mis a jour: ${THEME_DISPLAY_NAME}"
    else
        echo -e "  ${YELLOW}⚠ Fichier style.css non trouve dans le theme${NC}"
    fi
}

# ─── wp-config.php (table_prefix + cles) ────────────────────────────

configure_wp_config() {
    local wp_config="$PROJECT_ROOT/web/wp-config.php"
    if [ ! -f "$wp_config" ]; then
        echo -e "  ${YELLOW}⚠ Fichier wp-config.php non trouve${NC}"
        return
    fi

    start_spinner "Configuration de wp-config.php (table_prefix + cles)..."
    TABLE_PREFIX=$(echo "$PROJECT_NAME" | tr -d 'aeiouAEIOU' | tr -d '_-')_

    generate_key() { openssl rand -base64 48 | tr -d '\n/+=' | head -c 64; }

    local wp_keys=("AUTH_KEY" "SECURE_AUTH_KEY" "LOGGED_IN_KEY" "NONCE_KEY" "AUTH_SALT" "SECURE_AUTH_SALT" "LOGGED_IN_SALT" "NONCE_SALT")
    local sed_expr="s/^\\\$table_prefix.*=.*'\(.*\)';/\\\$table_prefix  = '${TABLE_PREFIX}';/;"
    local keys_generated=0

    for key_name in "${wp_keys[@]}"; do
        local current_value
        current_value=$(grep "define('${key_name}'" "$wp_config" | sed "s/.*define('${key_name}',[[:space:]]*'\([^']*\)').*/\1/")
        if [ -z "$current_value" ]; then
            local key_value
            key_value=$(generate_key)
            sed_expr+="s|define('${key_name}',[[:space:]]*'[^']*');|define('${key_name}', '${key_value}');|;"
            ((keys_generated++))
        fi
    done

    local current_maintenance
    current_maintenance=$(grep "define('STARTER_MAINTENANCE_KEY'" "$wp_config" | sed "s/.*define('STARTER_MAINTENANCE_KEY',[[:space:]]*'\([^']*\)').*/\1/")
    if [ -z "$current_maintenance" ]; then
        MAINTENANCE_KEY=$(generate_key)
        sed_expr+="s|define('STARTER_MAINTENANCE_KEY',[[:space:]]*'[^']*');|define('STARTER_MAINTENANCE_KEY', '${MAINTENANCE_KEY}');|;"
        ((keys_generated++))
    else
        MAINTENANCE_KEY="$current_maintenance"
    fi

    portable_sed "$sed_expr" "$wp_config"
    if [ "$keys_generated" -gt 0 ]; then
        stop_spinner "ok" "wp-config.php configure (table_prefix=${TABLE_PREFIX}, ${keys_generated} cle(s) generee(s))"
    else
        stop_spinner "ok" "wp-config.php configure (table_prefix=${TABLE_PREFIX}, cles existantes conservees)"
    fi
}

# ─── 1Password ───────────────────────────────────────────────────────

save_to_1password() {
    local wp_admin_password="$1"
    local wp_url="$2"

    local op_vault op_url op_secret op_user
    op_vault=$(grep '^1PASSWORD_VAULT=' "$GLOBAL_ENV" 2>/dev/null | cut -d'=' -f2-)
    op_url=$(grep '^1PASSWORD_URL=' "$GLOBAL_ENV" 2>/dev/null | cut -d'=' -f2-)
    op_secret=$(grep '^1PASSWORD_SECRET=' "$GLOBAL_ENV" 2>/dev/null | cut -d'=' -f2-)
    op_user=$(grep '^1PASSWORD_USER=' "$GLOBAL_ENV" 2>/dev/null | cut -d'=' -f2-)
    op_vault="${op_vault:-Sites}"
    op_user="${op_user:-digital@lonsdale.fr}"

    if ! command -v op &>/dev/null; then
        echo -e "  ${YELLOW}⚠ CLI 1Password (op) non trouvee, mot de passe non sauvegarde${NC}"
        return
    fi

    if ! op whoami &>/dev/null; then
        local op_attempts=3
        while [ $op_attempts -gt 0 ] && ! op whoami &>/dev/null; do
            if [ -n "$op_url" ] && [ -n "$op_secret" ] && [ -n "$op_user" ]; then
                read -rsp "Mot de passe 1Password (${op_user}): " OP_PASSWORD </dev/tty
                echo "" >/dev/tty
                local op_signin_out
                op_signin_out=$(echo "$OP_PASSWORD" | op signin --account "$op_url" 2>/dev/null) || true
                if [ -z "$op_signin_out" ]; then
                    op_signin_out=$(echo "$OP_PASSWORD" | op account add --address "$op_url" --email "$op_user" --secret-key "$op_secret" --signin 2>/dev/null) || true
                fi
                [ -n "$op_signin_out" ] && eval "$op_signin_out" 2>/dev/null || true
            elif [ -n "$op_url" ]; then
                eval "$(op signin --account "$op_url" </dev/tty 2>/dev/tty)" 2>/dev/null || true
            else
                eval "$(op signin </dev/tty 2>/dev/tty)" 2>/dev/null || true
            fi

            if op whoami &>/dev/null; then
                break
            fi

            op_attempts=$((op_attempts - 1))
            if [ $op_attempts -gt 0 ]; then
                echo -e "  ${YELLOW}⚠ Authentification 1Password echouee, nouvelle tentative (${op_attempts} restante(s))...${NC}"
            fi
        done
    fi

    if ! op whoami &>/dev/null; then
        echo -e "  ${YELLOW}⚠ 1Password non authentifie, mot de passe non sauvegarde (le script continue)${NC}"
        return
    fi

    start_spinner "Sauvegarde du mot de passe dans 1Password..."
    local op_title="${PROJECT_NAME} - Local"

    if op item get "$op_title" --vault "$op_vault" </dev/null &>/dev/null; then
        if op item edit "$op_title" \
            --vault "$op_vault" \
            --url "${wp_url}" \
            "username=digital@lonsdale.fr" \
            "password=$wp_admin_password" </dev/null &>/dev/null; then
            stop_spinner "ok" "Mot de passe mis a jour dans 1Password (vault: $op_vault)"
        else
            stop_spinner "warn" "Mise a jour 1Password echouee"
        fi
    else
        if op item create \
            --category login \
            --vault "$op_vault" \
            --title "$op_title" \
            --url "${wp_url}" \
            "username=digital@lonsdale.fr" \
            "password=$wp_admin_password" </dev/null &>/dev/null; then
            stop_spinner "ok" "Mot de passe sauvegarde dans 1Password (vault: $op_vault)"
        else
            stop_spinner "warn" "Sauvegarde 1Password echouee"
        fi
    fi
}

# ─── Installation WordPress ─────────────────────────────────────────

install_wordpress() {
    local wp_container="${PROJECT_NAME}-wp"
    local wp_url="https://${PROJECT_NAME}.code"

    if docker exec "$wp_container" wp core is-installed --allow-root 2>/dev/null; then
        echo -e "  ${YELLOW}⚠ WordPress est deja installe, installation ignoree${NC}"
        return
    fi

    start_spinner "Installation de WordPress..."
    local wp_admin_password
    wp_admin_password=$(openssl rand -base64 12 | tr -d '\n/+=' | head -c 16)

    if ! docker exec "$wp_container" wp core install \
        --url="$wp_url" \
        --title="$PROJECT_NAME" \
        --admin_user=digital@lonsdale.fr \
        --admin_email=digital@lonsdale.fr \
        --admin_password="$wp_admin_password" \
        --skip-email \
        --allow-root 2>/dev/null; then
        stop_spinner "error" "Erreur lors de l'installation WordPress"
        return
    fi

    stop_spinner "ok" "WordPress installe (${wp_url})"
    WP_ADMIN_PASSWORD="$wp_admin_password"

    save_to_1password "$wp_admin_password" "${wp_url}/${PROJECT_NAME}-access"
}

# ─── WPML ────────────────────────────────────────────────────────────

handle_wpml() {
    local wp_container="${PROJECT_NAME}-wp"
    local plugins_dir="$PROJECT_ROOT/web/wp-content/plugins"
    WPML_JUST_DOWNLOADED=false

    if $MULTILANG; then
        local cms_exists=false st_exists=false
        [ -d "$plugins_dir/sitepress-multilingual-cms" ] && cms_exists=true
        [ -d "$plugins_dir/wpml-string-translation" ] && st_exists=true

        if $cms_exists && $st_exists; then
            echo -e "  ${GREEN}✓ Plugins WPML deja presents, telechargement ignore${NC}"
            return
        fi

        local wpml_user_id wpml_sub_key wpml_sub_key_encoded
        wpml_user_id=$(grep '^WPML_USER_ID=' "$ENV_FILE" | cut -d'=' -f2- | tr -d "'" | tr -d '"')
        wpml_sub_key=$(grep '^WPML_SUBSCRIPTION_KEY=' "$ENV_FILE" | cut -d'=' -f2- | tr -d "'" | tr -d '"')
        wpml_sub_key_encoded=$(printf '%s' "$wpml_sub_key" | python3 -c "import sys, urllib.parse; print(urllib.parse.quote(sys.stdin.read()))" 2>/dev/null || printf '%s' "$wpml_sub_key")

        if [ -z "$wpml_user_id" ] || [ -z "$wpml_sub_key" ]; then
            echo -e "  ${YELLOW}⚠ Cles WPML non trouvees dans .env, installation WPML ignoree${NC}"
            return
        fi

        start_spinner "Installation des plugins WPML..."
        local cms_url="https://wpml.org/?download=6088&user_id=${wpml_user_id}&subscription_key=${wpml_sub_key_encoded}&t=$(date +%s)"
        local st_url="https://wpml.org/?download=6092&user_id=${wpml_user_id}&subscription_key=${wpml_sub_key_encoded}&t=$(date +%s)"

        if docker exec "$wp_container" bash -c "curl -sL '${cms_url}' -o /tmp/sitepress-multilingual-cms.zip & curl -sL '${st_url}' -o /tmp/wpml-string-translation.zip & wait && wp plugin install /tmp/sitepress-multilingual-cms.zip --force --allow-root && wp plugin install /tmp/wpml-string-translation.zip --force --allow-root && rm -f /tmp/sitepress-multilingual-cms.zip /tmp/wpml-string-translation.zip" 2>/dev/null; then
            stop_spinner "ok" "Plugins WPML installes"
            WPML_JUST_DOWNLOADED=true
        else
            stop_spinner "error" "Erreur lors de l'installation des plugins WPML"
        fi
    else
        if [ -d "$plugins_dir/sitepress-multilingual-cms" ] || [ -d "$plugins_dir/wpml-string-translation" ]; then
            start_spinner "Suppression des plugins WPML..."
            rm -rf "$plugins_dir/sitepress-multilingual-cms" "$plugins_dir/wpml-string-translation"
            stop_spinner "ok" "Plugins WPML supprimes"
        fi
    fi
}

# ─── Activation theme + plugins + options ────────────────────────────

activate_theme_and_plugins() {
    local wp_container="${PROJECT_NAME}-wp"
    local plugins="advanced-custom-fields-pro wps-hide-login wps-limit-login wordpress-seo"
    local plugins_label="ACF, WPS Hide Login, WPS Limit Login, Yoast SEO"

    if $MULTILANG; then
        plugins="$plugins sitepress-multilingual-cms wpml-string-translation"
        plugins_label="$plugins_label, WPML, WPML String Translation"
    fi

    local acf_license whl_slug acf_cmd=""
    acf_license=$(grep '^ACF_PRO_LICENSE=' "$ENV_FILE" | cut -d'=' -f2)
    whl_slug="${PROJECT_NAME}-access"

    if [ -n "$acf_license" ]; then
        local acf_json acf_b64
        acf_json=$(printf '{"key":"%s","url":"https://%s.code"}' "$acf_license" "$PROJECT_NAME")
        acf_b64=$(echo -n "$acf_json" | base64)
        acf_cmd="wp option update acf_pro_license '${acf_b64}' --allow-root;"
    fi

    start_spinner "Activation theme, plugins et options..."
    if docker exec "$wp_container" bash -c "
        wp theme activate '$PROJECT_NAME' --allow-root &&
        wp plugin activate $plugins --allow-root &&
        ${acf_cmd}
        wp option update whl_page '$whl_slug' --allow-root
    " 2>/dev/null; then
        stop_spinner "ok" "Theme ${PROJECT_NAME} active, plugins actives ($plugins_label), options configurees"
    else
        stop_spinner "error" "Erreur lors de l'activation theme/plugins"
    fi
}

# ─── Mise a jour WordPress core + plugins ────────────────────────────

update_wp_core_and_plugins() {
    local wp_container="${PROJECT_NAME}-wp"

    start_spinner "Mise a jour WordPress core et plugins..."

    local acf_update_cmd="" acf_license
    acf_license=$(grep '^ACF_PRO_LICENSE=' "$ENV_FILE" | cut -d'=' -f2)
    if [ -n "$acf_license" ]; then
        local acf_url="https://connect.advancedcustomfields.com/v2/plugins/download?p=pro&k=${acf_license}"
        acf_update_cmd="curl -sL '${acf_url}' -o /tmp/acf-pro.zip && wp plugin install /tmp/acf-pro.zip --force --allow-root && rm -f /tmp/acf-pro.zip;"
    fi

    local wpml_update_cmd=""
    if $MULTILANG && ! $WPML_JUST_DOWNLOADED; then
        local wpml_user_id wpml_sub_key wpml_sub_key_encoded
        wpml_user_id=$(grep '^WPML_USER_ID=' "$ENV_FILE" | cut -d'=' -f2- | tr -d "'" | tr -d '"')
        wpml_sub_key=$(grep '^WPML_SUBSCRIPTION_KEY=' "$ENV_FILE" | cut -d'=' -f2- | tr -d "'" | tr -d '"')
        wpml_sub_key_encoded=$(printf '%s' "$wpml_sub_key" | python3 -c "import sys, urllib.parse; print(urllib.parse.quote(sys.stdin.read()))" 2>/dev/null || printf '%s' "$wpml_sub_key")
        if [ -n "$wpml_user_id" ] && [ -n "$wpml_sub_key" ]; then
            local cms_url="https://wpml.org/?download=6088&user_id=${wpml_user_id}&subscription_key=${wpml_sub_key_encoded}&t=\$(date +%s)"
            local st_url="https://wpml.org/?download=6092&user_id=${wpml_user_id}&subscription_key=${wpml_sub_key_encoded}&t=\$(date +%s)"
            wpml_update_cmd="curl -sL '${cms_url}' -o /tmp/sitepress-multilingual-cms.zip && wp plugin install /tmp/sitepress-multilingual-cms.zip --force --allow-root && rm -f /tmp/sitepress-multilingual-cms.zip && curl -sL '${st_url}' -o /tmp/wpml-string-translation.zip && wp plugin install /tmp/wpml-string-translation.zip --force --allow-root && rm -f /tmp/wpml-string-translation.zip;"
        fi
    fi

    local update_output
    update_output=$(docker exec "$wp_container" bash -c "
        BEFORE=\$(wp core version --allow-root 2>/dev/null);
        wp core update --allow-root 2>/dev/null;
        wp core update-db --allow-root 2>/dev/null;
        AFTER=\$(wp core version --allow-root 2>/dev/null);
        wp plugin update --all --allow-root 2>/dev/null;
        ${acf_update_cmd}
        ${wpml_update_cmd}
        echo \"WP_CORE:\${BEFORE}:\${AFTER}\";
    " 2>/dev/null)

    local wp_before wp_after
    wp_before=$(echo "$update_output" | grep '^WP_CORE:' | cut -d: -f2 | tr -d '\r')
    wp_after=$(echo "$update_output" | grep '^WP_CORE:' | cut -d: -f3 | tr -d '\r')

    if [ "$wp_before" != "$wp_after" ]; then
        stop_spinner "ok" "WordPress mis a jour (${wp_before} → ${wp_after}), plugins mis a jour"
    else
        stop_spinner "ok" "WordPress a jour (${wp_before}), plugins mis a jour"
    fi
}

# ═════════════════════════════════════════════════════════════════════
# Main
# ═════════════════════════════════════════════════════════════════════

prompt_project_info "$1"
echo -e "${YELLOW}Initialisation du projet: ${PROJECT_NAME}${NC}"

configure_git_remote
configure_env_file
copy_compose_sample

docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" build --parallel &>/dev/null &
DOCKER_BUILD_PID=$!
BG_PIDS+=($DOCKER_BUILD_PID)

rename_theme
update_sonar_props
setup_local_infra
update_theme_name
configure_wp_config

WP_CONTAINER="${PROJECT_NAME}-wp"
start_spinner "Build des images Docker..."
wait $DOCKER_BUILD_PID
stop_spinner "ok" "Images Docker construites"

start_spinner "Lancement des conteneurs..."
docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" up -d &>/dev/null
stop_spinner "ok" "Conteneurs lances"

start_spinner "Attente du conteneur WordPress (${WP_CONTAINER})..."
RETRIES=30
until docker exec "$WP_CONTAINER" wp --allow-root --info &>/dev/null || [ $RETRIES -eq 0 ]; do
    sleep 2
    RETRIES=$((RETRIES - 1))
done

if [ $RETRIES -gt 0 ]; then
    stop_spinner "ok" "Conteneur WordPress pret"

    install_wordpress
    handle_wpml
    activate_theme_and_plugins
    update_wp_core_and_plugins

    WP_URL="https://${PROJECT_NAME}.code/${PROJECT_NAME}-access"
    start_spinner "Ouverture de ${WP_URL}..."
    sleep 2
    open_url "$WP_URL"
    stop_spinner "ok" "Navigateur ouvert"
else
    stop_spinner "error" "Le conteneur WordPress n'est pas pret apres 60s"
fi

echo ""
echo -e "${GREEN}✓ Initialisation terminee avec succes!${NC}"
echo -e "  Projet: ${PROJECT_NAME}"
echo -e "  Theme: web/wp-content/themes/${PROJECT_NAME}"
echo -e "  Theme Name: ${THEME_DISPLAY_NAME}"
echo -e "  Table prefix: ${TABLE_PREFIX}"
echo -e "  Base de donnees: ${DB_NAME}"
echo -e "  Admin URL: ${WP_URL}"
echo -e "  Admin: digital@lonsdale.fr"
[ -n "$WP_ADMIN_PASSWORD" ] && echo -e "  Password: ${WP_ADMIN_PASSWORD}"
echo -e "  Maintenance Key: ${MAINTENANCE_KEY}"

trap - INT TERM
echo ""
echo -e "${GREEN}Lancement des conteneurs en mode attache (Ctrl+C pour arreter)...${NC}"
docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" up

exit $?
}
