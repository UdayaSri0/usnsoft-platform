# Release Hardening Checklist

## Before Deploy

- `APP_DEBUG=false` outside local
- `USNSOFT_ENFORCE_INTERNAL_MFA=true` in staging/production
- anti-spam provider enabled for public forms
- queue worker and scheduler process definitions updated
- backups are current
- restore path has been tested recently

## After Deploy

- migrations completed
- queues restarted
- `/up` returns success
- login works
- staff MFA challenge works
- security center opens for authorized roles
- protected downloads and applicant/request file access work
- no unexpected 403s on existing approval/content/product flows
