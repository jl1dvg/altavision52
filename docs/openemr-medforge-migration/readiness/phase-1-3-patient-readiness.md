# Fase 1.3 - readiness Patient V1

Estado: Draft.
Fecha local Ecuador: 2026-07-30.
Fecha UTC de ejecucion remota: 2026-07-31.

## Resumen

Patient V1 no puede pasar a Accepted todavia.

La verificacion estructural de OpenEMR/Alta Vision avanzo: se accedio al VPS por SSH, se identifico el vhost activo y se ejecuto una auditoria agregada sobre la base configurada por OpenEMR sin extraer PII y sin escribir datos.

Bloqueantes vigentes:

- PR P0 independiente de MedForge para corregir la unicidad polimorfica de `core_external_aliases`.
- Preparacion formal de organizacion e instancias Alta Vision en Control Center.
- Fixtures sinteticos del piloto.
- Revision final de Jorge para pasar `patient-v1` de Draft a Accepted.

## Fuentes verificadas

- SSH operativo: alias `altavision-vps`.
- Alias no operativo en este entorno: `altavision_vps`.
- Vhost Alta Vision: `app.alta-vision.com`.
- Document root: `/var/www/html/altavision`.
- Config OpenEMR: `/var/www/html/altavision/sites/default/sqlconf.php`.
- Esquema activo: documentado por hash `defe7bcf4b072b0b`.
- Conexion DB: `ok`.
- Modo: lectura/metadatos/agregados solamente.
- PII publicada: ninguna.

## Tablas reales

Presentes:

- `patient_data`.
- `history_data`.
- `insurance_data`.
- `form_encounter`.
- `forms`.
- `documents`.
- `categories_to_documents`.
- `openemr_postcalendar_events`.
- `billing`.
- `transactions`.
- `lists`.
- `pnotes`.

No presentes en el esquema activo:

- `appointments`.
- `consulta_data`.
- `procedimiento_proyectado`.
- `protocolo_data`.

## Estructura patient_data

Indices verificados:

- `pid`: unique sobre `pid`.
- `id`: indice no unique sobre `id`.

Columnas relevantes:

- `pid`, `id`, `pubpid`.
- `fname`, `mname`, `lname`, `lname2`.
- `DOB`, `sex`.
- `ss`, `email`, `phone_home`, `phone_biz`, `phone_cell`, `phone_contact`.
- `status`, `deceased_date`, `deceased_reason`.
- `genericname1`, `genericval1`, `genericname2`, `genericval2`.
- `hipaa_allowwhatsapp`.

Hallazgos estructurales:

- `hc_number` no existe en `patient_data`.
- `lname2` existe en la base real y no aparece en el `CREATE TABLE patient_data` base de `sql/database.sql`; se clasifica como personalizacion real o drift aplicado.
- `hipaa_allowwhatsapp` existe y esta versionado en `sql/6_0_0-to-whatsapp_allow-idempotent.sql`.

## Metricas agregadas

Pacientes:

- Total `patient_data`: 22.653.
- `pid`: 22.653 distintos, 0 vacios, 0 duplicados.
- `id`: 22.653 distintos, 0 vacios, 0 duplicados.
- `pubpid`: 5 vacios, 22.618 distintos no vacios, 30 grupos duplicados, 60 filas en grupos duplicados.
- `ss`: 22.653 vacios.
- `hc_number`: no existe.

Calidad demografica/contacto:

- `DOB`: 32 vacios, 6 futuros, 10 anteriores a 1900, rango agregado de anios 149-2067.
- `sex`: 264 vacios, 2 valores distintos no vacios.
- `email`: 3.266 vacios, 10.149 con forma invalida, 593 grupos duplicados.
- `phone_cell`: 1.694 vacios, 1.167 grupos duplicados.
- `deceased_date`: 0 con valor.
- `status`: 5 valores distintos no vacios.

Relaciones:

- `form_encounter`: 84.971 filas, 20.936 pacientes enlazados.
- `forms`: 233.128 filas, 20.936 pacientes enlazados.
- `documents`: 14.997 filas.
- `openemr_postcalendar_events`: 95.974 filas, 21.242 pacientes enlazados.
- `billing`: 906.240 filas, 10.525 pacientes enlazados.
- `transactions`: 5.469 filas, 3.131 pacientes enlazados.
- `lists`: 22.331 filas, 12.038 pacientes enlazados.
- `pnotes`: 19 filas, 11 pacientes enlazados.

## Implicaciones para Patient V1

- `openemr_pid` puede ser alias determinista principal.
- `openemr_patient_data_id` puede ser alias determinista secundario.
- `openemr_pubpid` no puede usarse como llave unica por duplicados y vacios.
- Cedula no puede validarse desde `ss` en esta fuente porque esta vacia en todos los pacientes verificados.
- `hc_number` no puede mapearse desde `patient_data` porque la columna no existe.
- Datos de contacto y fechas requieren reglas de normalizacion y revision, no deben bloquear identidad soberana salvo criterios clinicos aprobados.
- Billing existe con alto volumen, pero sigue fuera de `patient-v1`; se reserva para `billing-v1`.

## Readiness

| Criterio | Estado |
|---|---|
| Esquema real OpenEMR verificado sin PII | Parcialmente cumplido |
| Metricas agregadas de calidad documentadas | Cumplido para Pacientes |
| Personalizaciones reales identificadas | Parcialmente cumplido |
| PR P0 aliases MedForge | Pendiente |
| Control Center Alta Vision | Pendiente |
| Fixtures sinteticos piloto | Pendiente |
| Patient V1 Accepted | No |

Decision de readiness: mantener `patient-v1` en Draft.
