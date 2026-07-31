# Patient V1 - contrato consolidado

Estado: Draft.
Decision de Jorge: revision con cambios aprobada; no se autoriza implementacion, piloto ni migracion.
Fecha de consolidacion: 2026-07-31.

## 0. Addendum Fase 1.3 - decisiones vigentes

Fecha local Ecuador: 2026-07-30.
Fecha UTC de artefactos remotos: 2026-07-31.

Este addendum prevalece sobre secciones anteriores si hay conflicto.

Decisiones arquitectonicas aprobadas por Jorge:

- Alta Vision utilizara una instancia clinica y una base de datos independientes. Puede compartir infraestructura fisica o VPS, pero no tablas clinicas ni base de datos clinica con otras organizaciones.
- `core_patients` no incorporara `organization_id` ni `instance_id`; el aislamiento clinico sera por despliegue/base de datos.
- Control Center registrara organizacion, instancia, ambiente y servicios de Alta Vision.
- `core_patients.id` sera la identidad clinica soberana.
- `pid`, `pubpid`, `hc_number`, `form_id` y demas identificadores externos seran aliases o senales externas.
- La cedula no sera restriccion `UNIQUE` directa en `core_patients`; sera senal fuerte de vinculacion solo cuando este normalizada y verificada.
- Coincidencias ambiguas quedan en revision manual; no se fusionan pacientes automaticamente.
- OpenEMR/Patient V1 debe reutilizar y extender la arquitectura Provider existente. No se autoriza crear un mecanismo paralelo de resolucion de identidad.

Slugs propuestos para Control Center:

- Organizacion: `alta-vision`.
- Instancia MedForge Staging: `altavision-staging`.
- Instancia MedForge Produccion: `altavision-production`.
- `source_system=openemr`.
- `source_instance=altavision-openemr-production`.
- No utilizar dato demo como identidad definitiva.

Estado de verificacion estructural OpenEMR:

- VPS accesible por SSH con alias `altavision-vps`; `altavision_vps` no resolvio en este entorno.
- Vhost activo: `app.alta-vision.com`.
- Document root activo: `/var/www/html/altavision`.
- Config DB OpenEMR: `/var/www/html/altavision/sites/default/sqlconf.php`.
- Esquema activo verificado por hash `defe7bcf4b072b0b`.
- Conexion DB: `ok`.
- Escrituras ejecutadas: ninguna.
- PII publicada: ninguna.
- Total `patient_data`: 22.653.

Hallazgos Pacientes sin PII:

- `patient_data.pid`: 22.653 distintos, 0 vacios, 0 duplicados. Alias deterministico principal `openemr_pid`.
- `patient_data.id`: 22.653 distintos, 0 vacios, 0 duplicados. Alias deterministico secundario `openemr_patient_data_id`.
- `patient_data.pubpid`: 5 vacios, 30 grupos duplicados, 60 filas en grupos duplicados. No puede ser llave unica.
- `patient_data.hc_number`: no existe.
- `patient_data.ss`: existe pero esta vacio en todos los pacientes verificados; no permite validar cedula desde esta fuente.
- `patient_data.lname2`: existe en base real y no esta en el `CREATE TABLE patient_data` base local; personalizacion/drift real.
- `patient_data.hipaa_allowwhatsapp`: existe y esta versionado en `sql/6_0_0-to-whatsapp_allow-idempotent.sql`.
- `DOB`: 32 vacios, 6 futuros, 10 anteriores a 1900; requiere normalizacion/revision.
- `email` y telefonos tienen vacios, duplicados y formas invalidas; no deben usarse como identidad soberana.

Relaciones agregadas:

- `form_encounter`: 84.971 filas, 20.936 pacientes enlazados.
- `forms`: 233.128 filas, 20.936 pacientes enlazados.
- `documents`: 14.997 filas.
- `openemr_postcalendar_events`: 95.974 filas, 21.242 pacientes enlazados.
- `billing`: 906.240 filas, 10.525 pacientes enlazados.
- `transactions`: 5.469 filas, 3.131 pacientes enlazados.
- `lists`: 22.331 filas, 12.038 pacientes enlazados.
- `pnotes`: 19 filas, 11 pacientes enlazados.

Tablas no presentes en el esquema activo:

- `consulta_data`.
- `procedimiento_proyectado`.
- `protocolo_data`.
- `appointments`.

Reglas de resolucion de identidad Patient V1:

- Resolver primero aliases exactos existentes en `core_external_aliases` filtrando por `aliasable_type=Patient`.
- Crear o vincular `core_patients.id` solo despues de una resolucion deterministica o revision aprobada.
- `openemr_pid` y `openemr_patient_data_id` son deterministas dentro de `source_instance=altavision-openemr-production`.
- `openemr_pubpid` es alias fuerte no deterministico por duplicados/vacios.
- Cedula normalizada/verificada es senal fuerte; si hay ambiguedad, `manual_review_status=required`.
- No fusionar pacientes automaticamente.
- Todo lote debe usar `batch_id`, `source_instance`, conteos agregados, fingerprints y modo idempotente.
- Reruns no deben duplicar pacientes ni aliases.

Bloqueantes para `Accepted`:

1. Aprobar o implementar el PR P0 independiente de MedForge que corrige la unicidad de `core_external_aliases`.
2. Preparar formalmente organizacion e instancias Alta Vision en Control Center.
3. Actualizar fixtures sinteticos del piloto.
4. Revision final de Jorge.

Decision de readiness Fase 1.3: Patient V1 permanece Draft.

## 1. Fuentes

- OpenEMR/AltaVision: `altavision52`, rama documental limpia `codex/altavision-patient-v1-contract-clean`; rama Fase 1.3 `codex/altavision-phase-1-3-patient-readiness`.
- PR OpenEMR/AltaVision vigente: `https://github.com/jl1dvg/altavision52/pull/56`. PR `#55` queda supersedido.
- MedForge: `origin/staging=08f85d29c9f011d3c139ab56e3a6f0082c7a6d4d`.
- Revision Claude Code: `https://github.com/jl1dvg/MedForge/pull/1065`, recomendacion `APPROVE WITH CHANGES`.
- Notion: Programa Alta Vision Migracion OpenEMR a MedForge, Operational Core, Control Center, ADR Provider.

No se copio PII a este contrato. No se extrajeron filas reales de pacientes.

## 2. Estado Draft

Patient V1 sigue Draft por estos bloqueantes:

- Base OpenEMR real verificada parcialmente por estructura y metricas agregadas desde VPS, sin PII y sin escrituras.
- Falta aprobar o implementar la correccion P0 de unicidad polimorfica de `core_external_aliases`.
- No se ha preparado ni registrado formalmente la organizacion/instancias reales de Alta Vision en Control Center.
- Falta definir fixtures sinteticos del piloto.
- No se autoriza implementacion ni piloto.

## 3. Decisiones aceptadas

- Alta Vision usara una instancia logica y base de datos independiente para informacion clinica.
- Puede compartir infraestructura fisica con otros despliegues.
- No se usara una base clinica multi-tenant compartida en esta fase.
- `core_patients` no incorporara `organization_id` ni `instance_id`; el aislamiento clinico se hara por despliegue y base de datos.
- Control Center registrara organizacion, instancia, ambiente y servicios de Alta Vision.
- Patient V1 reutilizara y extendera la arquitectura Provider existente.
- No se autoriza crear un subsistema paralelo de identidad de pacientes.
- `core_patients.id` es la identidad soberana del paciente en MedForge.
- `pid`, `pubpid`, `hc_number`, cedula y `form_id` son aliases o senales externas.
- `form_id` pertenece principalmente a formulario/episodio legacy; no debe ser identidad primaria de paciente.
- La cedula no sera restriccion `UNIQUE` directa en `core_patients`; sera senal fuerte de vinculacion solo cuando este normalizada y verificada.
- La preparacion de la instancia real de Alta Vision en Control Center esta autorizada; su ejecucion no.

## 4. Decisiones rechazadas

- Rechazado: base clinica multi-tenant compartida para Alta Vision en esta fase.
- Rechazado: crear `PatientContractImportService` o cualquier flujo paralelo que duplique `ProviderIdentityResolver`/`AliasResolution`.
- Rechazado: ejecutar piloto con datos reales o anonimizados antes de verificar OpenEMR.
- Rechazado: escribir en bases productivas desde esta fase.

## 5. Arquitectura destino

La solucion debe extender el patron Provider:

- Reutilizar `core_external_aliases`.
- Reutilizar o generalizar `ProviderIdentityResolver`.
- Reutilizar o generalizar `AliasResolution`, `ProviderSignal` y `ProviderResolution`.
- Mantener confirmacion/revision humana para conflictos.
- Mantener dry-run como default.

Campos logicos del contrato:

- `organization_id`: `control_center_organizations.id`, cuando exista.
- `instance_id`: `control_center_instances.id`, cuando exista.
- `organization_slug`: slug de organizacion, por ejemplo `alta-vision`.
- `instance_slug`: slug de instancia MedForge/Control Center, por ejemplo `altavision-production`.
- `environment`: `production` o `staging`.
- `source_system`: `openemr`.
- `source_instance`: identificador logico de origen, por ejemplo `altavision-openemr-production`.
- `external_type`: alias namespaced, por ejemplo `openemr_pid`, `openemr_pubpid`, `openemr_patient_data_id`, `hc_number`.
- `external_value`: valor normalizado; no se publica en Notion ni fixtures si contiene PII.

## 6. Regla de unicidad

Regla logica deseada para aliases Patient:

`aliasable_type + instance_slug + provider + external_type + external_id` debe resolver a un solo paciente.

Implementacion actual en MedForge:

- `core_external_aliases` usa `instance_slug`, `provider`, `external_type`, `external_id`.
- La restriccion vigente no incluye `aliasable_type`/`aliasable_id`.
- La tabla es polimorfica y ya la usan Patient y Sede.

Conclusion:

- Antes de escribir aliases Patient, debe corregirse la deuda P0 transversal de unicidad polimorfica.
- La nueva unicidad minima debe incluir `aliasable_type`, `instance_slug`, `provider`, `external_type`, `external_id`.
- No debe incluir `aliasable_id`.
- Mientras tanto, Patient V1 debe namespaciar `external_type` y filtrar por `aliasable_type` en todo resolver.

## 7. Mapping OpenEMR/AltaVision

| OpenEMR/AltaVision | MedForge propuesto | Estado |
|---|---|---|
| `patient_data.pid` | alias `openemr_pid` | Aceptado como alias externo |
| `patient_data.id` | alias `openemr_patient_data_id` | Aceptado como alias externo |
| `patient_data.pubpid` | alias `openemr_pubpid` | Aceptado como alias externo |
| `patient_data.fname/mname/lname` | `core_patients.full_name` + datos demograficos de contrato | Draft |
| `patient_data.lname2` | `core_patients.second_last_name` o componente de nombre | Personalizacion real verificada |
| `patient_data.DOB` | `core_patients.birth_date` | Draft |
| `patient_data.sex` | `core_patients.sex` | Pendiente catalogo Jorge |
| `patient_data.phone_*`, `email` | perfil/contactos fuera de Core minimo | Draft |
| `patient_data.pricelevel`, `financial`, `billing_note` | fuera de Patient V1; futuro `billing-v1` | Aceptado fuera de alcance |
| `forms.form_id` | alias de formulario/episodio, no paciente | Aceptado |
| `patient_data.hc_number` | alias `hc_number` | No existe en base real verificada |
| `patient_data.hipaa_allowwhatsapp` | consentimiento/contactabilidad | Personalizacion real versionada |

## 8. Verificacion OpenEMR

OpenEMR inicializa DB asi:

1. `interface/globals.php` define `OE_SITE_DIR`.
2. `library/sqlconf.php` carga `sites/default/sqlconf.php`.
3. `sites/default/sqlconf.php` define parametros de conexion.

No exponer credenciales en logs, commits, Notion ni respuestas.

La Fase 1.3 ya verifico estructura y metricas agregadas del esquema activo de Pacientes. Si se requiere reproducibilidad externa adicional, el artefacto preferido sigue siendo:

```bash
mysqldump --no-data --skip-comments --single-transaction --default-character-set=utf8mb4 -u READONLY_USER -p -h DB_HOST --port=DB_PORT DB_NAME > altavision-openemr-schema.sql
```

Metricas agregadas permitidas sin PII:

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

## 9. Handoff MedForge futuro

No implementar todavia.

Cuando Jorge autorice implementacion:

- Extender el patron `ProviderIdentityResolver`; no crear subsistema paralelo.
- Validar `patient-v1.schema.json`.
- Rechazar `instance_slug` que no exista en Control Center.
- Ejecutar dry-run por defecto.
- Reportar conteos, fingerprints y razones de revision; nunca PII.
- Bloquear duplicados de alias.
- No cambiar lecturas legacy por `hc_number`.
- No tocar Billing, Agenda, WhatsApp, Solicitudes, Cirugias ni Reporting en esta fase.

Archivos probables MedForge:

- `laravel-app/app/Modules/Core/Services/PatientIdentityResolver.php` o generalizacion equivalente.
- `laravel-app/app/Console/Commands/PatientsValidateContract.php`.
- Tests bajo `laravel-app/tests/Feature/Core/`.
- La migracion correctiva de `core_external_aliases` debe ir en PR P0 independiente antes del importador OpenEMR.
- Especificacion autocontenida: `docs/openemr-medforge-migration/handoffs/medforge-core-alias-uniqueness-p0.md`.

## 10. Preparacion Control Center autorizada

Autorizado:

- Preparar comandos, parametros y checklist para registrar Alta Vision como organizacion/instancia.
- Revisar superficie vigente de Control Center.
- Documentar valores requeridos.

No autorizado:

- Ejecutar `instance:create`.
- Crear registros reales en Control Center.
- Desplegar infraestructura.

Propuesta formal:

- Organizacion: `alta-vision`.
- Instancia MedForge Staging: `altavision-staging`.
- Instancia MedForge Produccion: `altavision-production`.
- Ambiente Staging: `staging`.
- Ambiente Produccion: `production`.
- Sistema fuente: `openemr`.
- Instancia fuente: `altavision-openemr-production`.
- No usar dato demo como identidad definitiva.

Superficie MedForge verificada contra repo local:

- Ruta crear organizacion: `POST /v2/control-center/organizations`.
- Ruta crear instancia: `POST /v2/control-center/instances`.
- Controlador: `laravel-app/app/Modules/ControlCenter/Http/Controllers/ControlCenterApiController.php`.
- Servicio: `laravel-app/app/Modules/ControlCenter/Services/ControlCenterService.php`.
- No se ejecuto ninguna llamada ni se crearon registros reales.

## 11. Riesgos

| Riesgo | Estado | Mitigacion |
|---|---|---|
| DB OpenEMR no verificada | Parcialmente cerrado | estructura y metricas agregadas verificadas por VPS, sin PII |
| `pubpid` duplicado | Abierto | no usar como llave deterministica unica; alias fuerte con revision |
| Fechas DOB inconsistentes | Abierto | normalizacion y reglas de rechazo/revision en piloto sintetico |
| Contactos/email inconsistentes | Abierto | fuera de identidad soberana; limpiar en dominio contactos futuro |
| PII en artefactos | Controlado | solo estructura/agregados; fixtures sinteticos |
| Colision polimorfica en `core_external_aliases` | P0 abierto | PR independiente Core antes de importador |
| Subsistema paralelo de identidad | Rechazado | extender Provider/Identity resolver |
| CIVE/SigCenter heredado por Alta Vision | Controlado en diseno | instancia explicita, fail-closed |
| Facturacion mezclada con Patient | Controlado | fuera de alcance; futuro `billing-v1` |

## 12. Criterios para pasar a Accepted

- Esquema real OpenEMR verificado y documentado sin PII. Estado: parcialmente cumplido en Fase 1.3.
- Calidad de datos agregada documentada sin PII. Estado: cumplido para Pacientes.
- Columnas personalizadas reales identificadas. Estado: parcialmente cumplido; `lname2` y `hipaa_allowwhatsapp` verificados.
- Instancia Alta Vision preparada en Control Center y aprobada para ejecucion. Estado: pendiente.
- Deuda P0 de unicidad de `core_external_aliases` aprobada o implementada. Estado: pendiente.
- Contrato actualizado conforme a arquitectura Provider. Estado: cumplido documentalmente, pendiente revision MedForge despues del PR P0.
- Fixtures sinteticos del piloto definidos. Estado: pendiente.
- Jorge aprueba pasar de Draft a Accepted. Estado: pendiente.

## 13. Rollback

Dry-run: descartar artefactos temporales y reportes locales.

Si en una fase futura Jorge autoriza escritura en ambiente temporal:

1. Registrar `batch_id` o `source_fingerprint`.
2. Revertir aliases del batch.
3. Revertir datos derivados del batch.
4. Eliminar solo pacientes sin vinculos externos al batch.
5. Bloquear borrado si hay episodios, facturas, consultas, protocolos o aliases ajenos al batch.

Reconciliacion:

- Todo batch genera conteos por `created`, `matched_existing`, `manual_review_required`, `rejected`, `unchanged`.
- Un rerun con el mismo `batch_id` y fingerprints no debe duplicar aliases ni pacientes.
- Cualquier divergencia entre OpenEMR y MedForge queda como evento de reconciliacion, no como overwrite automatico.

## 14. Piloto 20 pacientes

No autorizado todavia.

Plan cuando se autorice:

- 5 pacientes solo demografia.
- 5 pacientes con formularios `eye_mag`.
- 5 pacientes con procedimiento/protocolo.
- 5 pacientes con senales de facturacion/afiliacion.

Todos los ejemplos y fixtures deben ser sinteticos o anonimizados; cero PII.
