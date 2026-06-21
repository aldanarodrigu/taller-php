variable "aws_region" {
  type        = string
  description = "AWS region for all resources"
  default     = "us-east-2"
}

variable "project_name" {
  type        = string
  description = "Prefix used in all resource names and tags"
  default     = "taller-php"
}

variable "ec2_instance_type" {
  type        = string
  description = "EC2 instance type for the backend"
  default     = "t3.small"
}

variable "rds_instance_class" {
  type        = string
  description = "RDS instance class for PostgreSQL"
  default     = "db.t3.micro"
}

variable "db_name" {
  type        = string
  description = "PostgreSQL database name"
  default     = "taller_php"
}

variable "db_username" {
  type        = string
  description = "PostgreSQL master username"
  default     = "postgres"
}

variable "db_password" {
  type        = string
  description = "PostgreSQL master password (store in terraform.tfvars, never commit)"
  sensitive   = true
}

variable "frontend_bucket_name" {
  type        = string
  description = "S3 bucket name for frontend (must be globally unique)"
}
