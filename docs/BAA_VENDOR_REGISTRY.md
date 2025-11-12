# Vendor Inventory Registry

This file lists all vendors/services that interact with ePHI and require a Business Associate Agreement (BAA).

| Vendor/Service | Type (Host/DB/Mail/etc.) | ePHI Access | BAA Status | Notes |
| Example: AWS | Hosting/DB/Backup | Yes | Signed | S3, RDS, EC2 |
| Example: SendGrid | Mail | Yes | Signed | Transactional email |
| Example: Twilio | Chat/SMS | Yes | Signed | HIPAA BAA on file |
| Example: Loggly | Logs | Yes | Signed | Log management |
| Example: Backblaze | Backup | Yes | Signed | Offsite backups |

Add more vendors/services as needed. Update BAA status and notes regularly.
