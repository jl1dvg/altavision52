# Auditoria Pacientes - OpenEMR/AltaVision a MedForge

Estado: `patient-v1` Draft. No aceptado.

## Resumen ejecutivo

El dominio Pacientes debe migrar hacia identidad clinica nativa de MedForge (`core_patients`) y conservar los identificadores legacy como aliases en `core_external_aliases`. En OpenEMR/AltaVision, la identidad operacional primaria es `patient_data.pid`; el identificador visible puede ser `patient_data.pubpid`. En MedForge legacy, muchas dependencias actuales usan `patient_data.hc_number` como llave de union hacia `consulta_data`, `procedimiento_proyectado`, `protocolo_data`, Billing, Agenda y WhatsApp.

Decision propuesta: MedForge no debe usar `hc_number`, `pid`, `pubpid` ni `form_id` como identidad soberana. Todos deben entrar como aliases externos scopiados por organizacion, instancia y fuente.

## Inicializacion DB OpenEMR

OpenEMR inicializa credenciales por sitio asi:

1. `interface/globals.php` define `OE_SITE_DIR`.
2. `library/sqlconf.php` carga `sites/default/sqlconf.php`.
3. `sites/default/sqlconf.php` define host, puerto, usuario, password y base.

No se deben imprimir ni commitear esos valores. En esta fase se verifico que `sites/default/sqlconf.php` existe, pero la conexion local fallo con `DB_ERROR_CODE=2002`; por tanto la base viva no fue auditada.

## Artefacto minimo requerido de Jorge

Opcion preferida, sin datos:

```bash
mysqldump --no-data --skip-comments --single-transaction --default-character-set=utf8mb4 -u READONLY_USER -p -h DB_HOST --port=DB_PORT DB_NAME > altavision-openemr-schema.sql
```

Opcion alternativa, solo metricas agregadas sin PII: ejecutar queries read-only equivalentes a:

```sql
SHOW COLUMNS FROM patient_data;
SHOW COLUMNS FROM forms;
SHOW COLUMNS FROM form_eye_base;
SHOW COLUMNS FROM form_eye_mag_orders;
SELECT COUNT(*) FROM patient_data;
SELECT COUNT(*) FROM patient_data WHERE pid IS NULL OR pid = 0;
SELECT COUNT(*) FROM (SELECT pid FROM patient_data GROUP BY pid HAVING COUNT(*) > 1) x;
SELECT COUNT(*) FROM patient_data WHERE pubpid IS NULL OR TRIM(pubpid) = '';
SELECT COUNT(*) FROM (SELECT pubpid FROM patient_data WHERE pubpid IS NOT NULL AND TRIM(pubpid) <> '' GROUP BY pubpid HAVING COUNT(*) > 1) x;
SELECT COUNT(*) FROM patient_data WHERE DOB IS NULL OR DOB = '0000-00-00';
SELECT COUNT(*) FROM patient_data WHERE DOB > CURDATE();
SELECT COUNT(*) FROM patient_data WHERE DOB IS NOT NULL AND DOB <> '0000-00-00' AND DOB < '1900-01-01';
SELECT COUNT(*) FROM patient_data WHERE sex IS NULL OR TRIM(sex) = '';
SELECT COUNT(*) FROM patient_data WHERE phone_cell IS NULL OR TRIM(phone_cell) = '';
SELECT COUNT(*) FROM patient_data WHERE email IS NULL OR TRIM(email) = '';
SELECT COUNT(*) FROM patient_data WHERE email IS NOT NULL AND TRIM(email) <> '' AND email NOT LIKE '%_@_%._%';
SELECT COUNT(*) FROM patient_data WHERE deceased_date IS NOT NULL OR TRIM(COALESCE(deceased_reason, '')) <> '';
```

## Hallazgos verificados

### OpenEMR/AltaVision

- `sql/database.sql` define `patient_data` con `id` autoincremental, `pid` unico, `pubpid`, nombres, DOB, sexo, telefonos, email, direccion, `providerID`, `ref_providerID`, `pricelevel` y `billing_note`.
- `forms.form_id` es la llave de formulario OpenEMR; varios reportes quirurgicos y oftalmologicos la usan como pivote.
- `form_eye_base.id` enlaza con `forms.form_id` y contiene `pid`, `date`, `user`, `groupname`, `authorized`, `activity`.
- `form_eye_mag_orders` y `form_eye_mag_impplan` usan `form_id` + `pid` para ordenes, procedimientos/planes e items oftalmologicos.
- `interface/forms/eye_mag/save.php` actualiza datos del paciente y crea ordenes/procedimientos alrededor de `form_id` y `pid`.

### MedForge

- `core_patients` en `origin/staging` contiene `id`, `full_name`, `birth_date`, `sex`, timestamps.
- `core_external_aliases` contiene `aliasable_type`, `aliasable_id`, `instance_slug`, `provider`, `external_type`, `external_id`; tiene unicidad por `instance_slug + provider + external_type + external_id`.
- `App\Models\PatientDatum` usa tabla legacy `patient_data` y declara `hc_number`, `cedula`, nombres, afiliacion, fecha_nacimiento, sexo, contactos, sede_principal, procedencia/referido y auditoria.
- `ConsultaDatum`, `ProcedimientoProyectado` y `ProtocoloDatum` se relacionan con `PatientDatum` por `hc_number`.
- `ProcedimientoProyectado` tiene scope `sigcenter_present`, por lo que parte del modelo actual es integracion legacy y no core global.
- Control Center tiene `control_center_organizations.id` y `control_center_instances.id`; la arquitectura vigente recomienda instancia por clinica, no multi-tenant por fila.
- La identidad de instancia debe ser explicita; un `CONTROL_CENTER_INSTANCE_SLUG` vacio debe fallar cerrado y no heredar defaults CIVE.

## Matriz de correspondencia inicial

| OpenEMR/AltaVision | MedForge actual | MedForge propuesto | Regla |
|---|---|---|---|
| `patient_data.pid` | N/A Core directo | `core_external_aliases.external_type=openemr_pid` | Alias externo obligatorio. |
| `patient_data.id` | N/A Core directo | `core_external_aliases.external_type=openemr_patient_data_id` | Alias externo si existe. |
| `patient_data.pubpid` | N/A Core directo | `core_external_aliases.external_type=openemr_pubpid` | Alias externo visible. |
| `patient_data.fname/mname/lname` | `patient_data.fname/mname/lname/lname2` | `core_patients.full_name` + campos normalizados futuros | En v1 Core solo conserva `full_name`; ampliar requiere ADR/Claude spec. |
| `patient_data.DOB` | `patient_data.fecha_nacimiento` | `core_patients.birth_date` | Normalizar fecha. |
| `patient_data.sex` | `patient_data.sexo` | `core_patients.sex` | Normalizar catalogo requiere decision clinica. |
| `patient_data.phone_*`, `email` | `patient_data.celular`, `telefono_alt`, `email` | Contacto fuera de `core_patients` o extension patient profile | No escribir en Core si no existe contrato aceptado. |
| `patient_data.pricelevel`, `billing_note` | Billing usa `hc_number` y afiliacion | Billing profile/coverage futuro | No mezclar con identidad clinica. |
| `forms.form_id` | `consulta_data.form_id`, `procedimiento_proyectado.form_id`, `protocolo_data.form_id` | `core_external_aliases.external_type=openemr_form_id` o alias de episodio | Alias de episodio/formulario, no alias de paciente salvo trazabilidad piloto. |
| MedForge `hc_number` | Llave legacy transversal | `core_external_aliases.external_type=hc_number` | Alias externo, no identidad soberana. |

## Modelo de alias propuesto

Campos obligatorios para vincular identidad:

- `organization_id`: `control_center_organizations.id`; puede ser `null` solo antes de registrar Altavision en Control Center.
- `instance_id`: `control_center_instances.id`; puede ser `null` solo antes de registrar Altavision en Control Center.
- `source_system`: `openemr`.
- `source_instance`: `altavision-openemr-production` o equivalente aprobado.
- `external_type`: tipo canonico del alias (`openemr_pid`, `openemr_pubpid`, `openemr_patient_data_id`, `hc_number`).
- `external_value`: valor normalizado del alias. No se publica en Notion ni fixtures si contiene PII.

Unicidad recomendada:

- `instance_id + source_system + source_instance + external_type + external_value` debe ser unico.
- Un mismo paciente puede tener multiples aliases.
- Un mismo alias no puede apuntar a dos pacientes; si ocurre, se bloquea write y se marca `requires_review`.
- Matches inferidos por nombre, telefono, email o fecha de nacimiento no hacen merge automatico.

## Vinculacion de pacientes existentes

1. Buscar alias exacto dentro de la misma instancia.
2. Si existe y apunta a un unico `core_patient`, vincular a ese paciente.
3. Si existe y apunta a multiples pacientes, bloquear y escalar.
4. Si no existe alias, crear candidato de paciente nuevo en dry-run.
5. Si hay similitud demografica sin alias exacto, reportar candidato duplicado, no fusionar.

## Riesgos

- Duplicados: `pid`, `pubpid`, `hc_number` y cedula pueden no ser equivalentes uno-a-uno.
- PHI: nombres, cedulas, telefonos, emails y direcciones no deben salir a Notion ni a commits.
- Facturacion: `pricelevel`, `financial`, afiliacion y billing notes requieren contrato propio; no bloquear Pacientes, pero no perder trazabilidad.
- Formularios: `form_id` identifica formularios/episodios legacy, no pacientes.
- `sigcenter_present`: dependencia de compatibilidad CIVE/SigCenter en MedForge; Altavision no debe heredar ese supuesto.

## Propuesta de identidad clinica nativa

1. Crear/usar `core_patients.id` como identidad soberana MedForge.
2. Registrar aliases por instancia:
   - `provider=openemr-altavision`, `external_type=openemr_pid`.
   - `provider=openemr-altavision`, `external_type=openemr_pubpid`.
   - `provider=openemr-altavision`, `external_type=openemr_patient_data_id`.
   - `provider=altavision-legacy`, `external_type=hc_number` cuando exista equivalencia validada.
3. Resolver duplicados por politica deterministica y cola de revision humana:
   - match exacto de `pid` existente: mismo paciente.
   - conflicto de `pid` con datos demograficos incompatibles: bloquear y revisar.
   - match solo por nombre/fecha nacimiento/telefono: candidato, no auto-merge.
4. Mantener `form_id` como alias de episodio/formulario, no de paciente.

## Piloto planificado: 20 pacientes anonimizados

Objetivo: validar contrato y resolucion de identidad sin migrar datos reales.

Entrada:
- 20 registros extraidos por query read-only.
- PII reemplazada por valores sinteticos deterministas.
- `pid`, `pubpid`, `hc_number` reemplazados por aliases reversibles solo en ambiente local seguro o hashes salteados no reversibles para artefactos.

Criterios de seleccion:
- 5 pacientes con solo datos demograficos.
- 5 pacientes con formularios `eye_mag`.
- 5 pacientes con procedimiento/protocolo asociado.
- 5 pacientes con senales de facturacion/afiliacion.

Salida esperada:
- 20 documentos JSON validos contra `patient-v1.schema.json`.
- Reporte de duplicados/conflictos sin PHI.
- Matriz de aliases creada en entorno dry-run o base temporal.
- Cero escrituras en bases reales.

Rollback del piloto:
- Si es dry-run, descartar artefactos temporales.
- Si Jorge autoriza write en ambiente temporal, registrar cada alias con `batch_id`/`source_fingerprint` y revertir eliminando solo registros de ese batch en orden inverso: aliases, perfiles derivados, pacientes sin otros vinculos.
- Nunca borrar un `core_patient` que tenga episodios, facturas, consultas o aliases ajenos al batch piloto.

## Decisiones pendientes para Jorge

- Si `cedula` debe guardarse cifrada en MedForge o solo como hash/alias protegido.
- Catalogo canonico de sexo/genero para Altavision.
- Politica de contactos: Core Patient vs modulo Patient Profile.
- Reglas de merge cuando `pid`, `pubpid` y `hc_number` no coinciden.
- Alcance exacto de facturacion en `patient-v1` vs contrato `billing-v1`.
- Confirmacion de `organization_slug`, `instance_slug`, `source_instance` y registro de Altavision en Control Center.
- Revision tecnica de Claude Code del handoff MedForge antes de cualquier implementacion.
