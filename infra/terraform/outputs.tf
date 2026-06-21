output "aws_region" {
  description = "AWS region"
  value       = var.aws_region
}

output "ec2_instance_id" {
  description = "EC2 instance ID (use as DEPLOY_SSM_INSTANCE_ID secret)"
  value       = aws_instance.backend.id
}

output "ec2_public_ip" {
  description = "Static public IP of the backend"
  value       = aws_eip.backend.public_ip
}

output "backend_url" {
  description = "Backend URL using nip.io (no custom domain needed)"
  value       = "https://${aws_eip.backend.public_ip}.nip.io"
}

output "rds_host" {
  description = "RDS PostgreSQL hostname (use as DB_HOST)"
  value       = split(":", aws_db_instance.postgres.endpoint)[0]
  sensitive   = true
}

output "cloudfront_domain" {
  description = "CloudFront distribution domain (use as frontend URL)"
  value       = "https://${aws_cloudfront_distribution.frontend.domain_name}"
}

output "cloudfront_id" {
  description = "CloudFront distribution ID (use as CLOUDFRONT_ID secret)"
  value       = aws_cloudfront_distribution.frontend.id
}

output "s3_bucket" {
  description = "S3 bucket name (use as S3_BUCKET secret)"
  value       = aws_s3_bucket.frontend.bucket
}

output "github_actions_key_id" {
  description = "AWS Access Key ID for GitHub Actions (use as AWS_ACCESS_KEY_ID secret)"
  value       = aws_iam_access_key.github_actions.id
  sensitive   = true
}

output "github_actions_secret" {
  description = "AWS Secret Access Key for GitHub Actions (use as AWS_SECRET_ACCESS_KEY secret)"
  value       = aws_iam_access_key.github_actions.secret
  sensitive   = true
}

output "summary" {
  description = "Quick reference of all values needed for GitHub secrets"
  sensitive   = true
  value = <<-EOT
    ============================================================
    GitHub Secrets – Backend repo (taller-php)
    ============================================================
    AWS_ACCESS_KEY_ID     = ${aws_iam_access_key.github_actions.id}
    AWS_SECRET_ACCESS_KEY = ${aws_iam_access_key.github_actions.secret}
    AWS_REGION            = ${var.aws_region}
    DEPLOY_SSM_INSTANCE_ID= ${aws_instance.backend.id}
    DEPLOY_PATH           = /var/www/taller-php

    ============================================================
    GitHub Secrets – Frontend repo (taller-php-frontend)
    ============================================================
    AWS_ACCESS_KEY_ID     = ${aws_iam_access_key.github_actions.id}
    AWS_SECRET_ACCESS_KEY = ${aws_iam_access_key.github_actions.secret}
    AWS_REGION            = ${var.aws_region}
    S3_BUCKET             = ${aws_s3_bucket.frontend.bucket}
    CLOUDFRONT_ID         = ${aws_cloudfront_distribution.frontend.id}
    VITE_APP_URL          = https://${aws_eip.backend.public_ip}.nip.io
    VITE_API_URL          = https://${aws_eip.backend.public_ip}.nip.io/api
    VITE_REVERB_HOST      = ${aws_eip.backend.public_ip}.nip.io
    VITE_REVERB_PORT      = 6001
    VITE_REVERB_SCHEME    = https
    ============================================================
  EOT
}
