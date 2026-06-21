# Deploy en AWS – Arquitectura desacoplada

Backend Laravel en EC2 · Frontend Vue/Vite en S3 + CloudFront · CI/CD via GitHub Actions

---

## Arquitectura

```
[GitHub push → main]
       │
       ├─ taller-php (backend)
       │    └─ GitHub Actions: CI → deploy via SSM → EC2
       │         EC2: Nginx + PHP 8.4-FPM + Reverb + Queue + Redis
       │         RDS: PostgreSQL 16
       │
       └─ taller-php-frontend (frontend)
            └─ GitHub Actions: CI → build Vite → S3 → CloudFront invalidation
```

---

## 1. Prerequisitos

- AWS CLI v2 configurado con credenciales de administrador
- Terraform >= 1.7
- GitHub CLI (`gh auth login`)
- Acceso de escritura a ambos repos en GitHub

---

## 2. Provisionar infraestructura con Terraform

```bash
cd infra/terraform
cp terraform.tfvars.example terraform.tfvars
# Editar terraform.tfvars con tus valores
terraform init
terraform plan
terraform apply
```

Terraform crea:
- EC2 t3.small + Elastic IP + IAM role para SSM
- RDS PostgreSQL 16 (db.t3.micro, no expuesto públicamente)
- S3 bucket + CloudFront para el frontend
- IAM user `taller-php-github-actions` con permisos mínimos
- Grupos de seguridad apropiados

---

## 3. Bootstrap inicial del EC2 (una sola vez)

Una vez que el EC2 esté up, ejecutar `bootstrap-app.sh` vía SSM:

```bash
# Obtener el instance ID
INSTANCE_ID=$(terraform -chdir=infra/terraform output -raw ec2_instance_id)

# Ejecutar bootstrap via SSM
aws ssm send-command \
  --instance-ids "$INSTANCE_ID" \
  --document-name "AWS-RunShellScript" \
  --parameters commands=["
    export REPO_URL='git@github.com:TU_ORG/taller-php.git'
    export APP_KEY='base64:GENERADO_CON_ARTISAN'
    export DB_HOST='$(terraform -chdir=infra/terraform output -raw rds_host)'
    export DB_PASSWORD='TU_DB_PASSWORD'
    export REVERB_APP_KEY='tu-reverb-key'
    export REVERB_APP_SECRET='tu-reverb-secret'
    bash /tmp/bootstrap-app.sh
  "] \
  --comment "Initial bootstrap"
```

O copiar y ejecutar el script directamente:
```bash
scp deploy/aws/bootstrap-app.sh ubuntu@<EC2_IP>:/tmp/
# Luego conectar por SSM Session Manager y ejecutar
```

---

## 4. Configurar secrets de GitHub Actions

```bash
chmod +x deploy/aws/configure-github-secrets.sh
./deploy/aws/configure-github-secrets.sh
```

El script lee los outputs de Terraform y configura automáticamente todos los secrets en ambos repos.

### Secrets del backend (taller-php)

| Secret | Descripción |
|---|---|
| `AWS_ACCESS_KEY_ID` | IAM key para GitHub Actions |
| `AWS_SECRET_ACCESS_KEY` | IAM secret para GitHub Actions |
| `AWS_REGION` | Región AWS (e.g. `us-east-2`) |
| `DEPLOY_SSM_INSTANCE_ID` | ID de la instancia EC2 |
| `DEPLOY_PATH` | `/var/www/taller-php` |

### Secrets del frontend (taller-php-frontend)

| Secret | Descripción |
|---|---|
| `AWS_ACCESS_KEY_ID` | Mismo IAM user |
| `AWS_SECRET_ACCESS_KEY` | Mismo IAM user |
| `AWS_REGION` | Región AWS |
| `S3_BUCKET` | Nombre del bucket S3 |
| `CLOUDFRONT_ID` | ID de la distribución CloudFront |
| `VITE_APP_URL` | URL del backend (https://IP.nip.io) |
| `VITE_API_URL` | `VITE_APP_URL/api` |
| `VITE_REVERB_KEY` | Reverb app key |
| `VITE_REVERB_HOST` | Hostname del backend |
| `VITE_REVERB_PORT` | `6001` |
| `VITE_REVERB_SCHEME` | `https` |

---

## 5. CI/CD – Flujo automático

Después de configurar los secrets, cada `push` a `main`:

- **Backend**: `ci.yml` (tests) → `deploy.yml` (SSM git pull + migrate + restart)
- **Frontend**: `ci.yml` (lint + build) → `deploy-to-s3.yml` (S3 sync + CloudFront invalidation)

---

## Archivos de configuración

| Archivo | Descripción |
|---|---|
| `user-data.sh` | Bootstrap de paquetes del EC2 (PHP 8.4, Nginx, Redis) |
| `bootstrap-app.sh` | Setup inicial de la app (clonar repo, .env, servicios) |
| `nginx/taller-php.conf` | Virtual host Nginx (PHP 8.4, WebSocket proxy) |
| `systemd/taller-php-queue.service` | Worker de colas |
| `systemd/taller-php-reverb.service` | Servidor WebSocket Reverb |
| `systemd/taller-php-scheduler.timer` | Scheduler de Laravel |
| `configure-github-secrets.sh` | Configurar secrets en ambos repos con gh CLI |
| `../../infra/terraform/` | Infraestructura completa como código |

