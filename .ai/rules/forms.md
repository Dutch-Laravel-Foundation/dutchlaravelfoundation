---
paths:
  - 'app/Content/Forms/**'
---

# Forms

## Statamic form storage
Form definitions and submissions belong to Statamic, not an invented Eloquent forms layer. Local storage/forms links to form-submissions; deployment links to shared persistent storage. Preserve existing validation/captcha and use only disposable submissions in tests, never production form data.
