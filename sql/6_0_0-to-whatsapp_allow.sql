# Optional schema update for WhatsApp consent
ALTER TABLE patient_data
  ADD COLUMN hipaa_allowwhatsapp VARCHAR(3) NOT NULL DEFAULT 'NO' AFTER hipaa_allowsms;
