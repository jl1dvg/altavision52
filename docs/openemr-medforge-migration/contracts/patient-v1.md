# Patient V1 - contrato consolidado

Estado: Draft.
Decision de Jorge: revision con cambios aprobada; no se autoriza implementacion, piloto ni migracion.
Fecha de consolidacion: 2026-07-31.

## 1. Fuentes

- OpenEMR/AltaVision: `altavision52`, rama documental `codex/altavision-patient-v1-contract`.
- PR OpenEMR/AltaVision: `https://github.com/jl1dvg/altavision52/pull/55`.
- MedForge: `origin/staging=08f85d29c9f011d3c139ab56e3a6f0082c7a6d4d`.
- Revision Claude Code: `https://github.com/jl1dvg/MedForge/pull/1065`, recomendacion `APPROVE WITH CHANGES`.
- Notion: Programa Alta Vision Migracion OpenEMR a MedForge, Operational Core, Control Center, ADR Provider.

No se copio PII a este contrato. No se extrajeron filas reales de pacientes.

## 2. Estado Draft

Patient V1 sigue Draft por estos bloqueantes:

- Base OpenEMR real no verificada. El acceso fue autorizado, pero la conexion local fallo con `DB_ERROR_CODE=2002`.
- Falta dump estructural sin datos, acceso read-only efectivo o metricas agregadas verificables.
- La deuda de unicidad polimorfica de `core_external_aliases` debe auditarse antes de modificar esquema.
- No se ha preparado ni registrado la instancia real de Alta Vision en Control Center.
- No se autoriza implementacion ni piloto.

## 3. Decisiones aceptadas

- Alta Vision usara una instancia logica y base de datos independiente para informacion clinica.
- Puede compartir infraestructura fisica con otros despliegues.
- No se usara una base clinica multi-tenant compartida en esta fase.
- Patient V1 reutilizara y extendera la arquitectura Provider existente.
- No se autoriza crear un subsistema paralelo de identidad de pacientes.
- `core_patients.id` es la identidad soberana del paciente en MedForge.
- `pid`, `pubpid`, `hc_number`, cedula y `form_id` son aliases o senales externas.
- `form_id` pertenece principalmente a formulario/episodio legacy; no debe ser identidad primaria de paciente.
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
- `instance_slug`: slug de instancia Control Center, por ejemplo `alta-vision-production`.
- `environment`: `production` o `staging`.
- `source_system`: `openemr`.
- `source_instance`: identificador logico de origen, por ejemplo `altavision-openemr-production`.
- `external_type`: alias namespaced, por ejemplo `openemr_pid`, `openemr_pubpid`, `openemr_patient_data_id`, `hc_number`.
- `external_value`: valor normalizado; no se publica en Notion ni fixtures si contiene PII.

## 6. Regla de unicidad

Regla logica deseada:

`instance_id + source_system + source_instance + external_type + external_value` debe resolver a un solo paciente.

Implementacion actual en MedForge:

- `core_external_aliases` usa `instance_slug`, `provider`, `external_type`, `external_id`.
- La restriccion vigente no incluye `aliasable_type`/`aliasable_id`.
- La tabla es polimorfica y ya la usan Patient y Sede.

Conclusion:

- Antes de modificar esquema o escribir aliases Patient, debe auditarse la deuda transversal de unicidad polimorfica.
- Cualquier cambio requiere ADR corta del Core porque afecta un contrato ya usado por Sede/CIVE.
- Mientras tanto, Patient V1 debe namespaciar `external_type` y filtrar por `aliasable_type` en todo resolver.

## 7. Mapping OpenEMR/AltaVision

| OpenEMR/AltaVision | MedForge propuesto | Estado |
|---|---|---|
| `patient_data.pid` | alias `openemr_pid` | Aceptado como alias externo |
| `patient_data.id` | alias `openemr_patient_data_id` | Aceptado como alias externo |
| `patient_data.pubpid` | alias `openemr_pubpid` | Aceptado como alias externo |
| `patient_data.fname/mname/lname` | `core_patients.full_name` + datos demograficos de contrato | Draft |
| `patient_data.DOB` | `core_patients.birth_date` | Draft |
| `patient_data.sex` | `core_patients.sex` | Pendiente catalogo Jorge |
| `patient_data.phone_*`, `email` | perfil/contactos fuera de Core minimo | Draft |
| `patient_data.pricelevel`, `financial`, `billing_note` | fuera de Patient V1; futuro `billing-v1` | Aceptado fuera de alcance |
| `forms.form_id` | alias de formulario/episodio, no paciente | Aceptado |
| MedForge `patient_data.hc_number` | alias `hc_number` | Aceptado como legacy |

## 8. Verificacion OpenEMR pendiente

OpenEMR inicializa DB asi:

1. `interface/globals.php` define `OE_SITE_DIR`.
2. `library/sqlconf.php` carga `sites/default/sqlconf.php`.
3. `sites/default/sqlconf.php` define parametros de conexion.

No exponer credenciales en logs, commits, Notion ni respuestas.

Artefacto preferido:

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
- Migracion correctiva de `core_external_aliases` solo despues de ADR/auditoria.

## 10. Preparacion Control Center autorizada

Autorizado:

- Preparar comandos, parametros y checklist para registrar Alta Vision como organizacion/instancia.
- Revisar `instance:create`.
- Documentar valores requeridos.

No autorizado:

- Ejecutar `instance:create`.
- Crear registros reales en Control Center.
- Desplegar infraestructura.

## 11. Riesgos

| Riesgo | Estado | Mitigacion |
|---|---|---|
| DB OpenEMR no verificada | Abierto | dump estructural o acceso read-only efectivo |
| PII en artefactos | Controlado | solo estructura/agregados; fixtures sinteticos |
| Colision polimorfica en `core_external_aliases` | Abierto prioritario | ADR + auditoria antes de modificar esquema |
| Subsistema paralelo de identidad | Rechazado | extender Provider/Identity resolver |
| CIVE/SigCenter heredado por Alta Vision | Controlado en diseno | instancia explicita, fail-closed |
| Facturacion mezclada con Patient | Controlado | fuera de alcance; futuro `billing-v1` |

## 12. Criterios para pasar a Accepted

- Dump estructural sin datos o acceso read-only verificado.
- Calidad de datos agregada documentada sin PII.
- Columnas personalizadas reales identificadas.
- Instancia Alta Vision preparada en Control Center y aprobada para ejecucion.
- Deuda de unicidad de `core_external_aliases` auditada y con ADR.
- Claude/Codex consolidados en este contrato unico.
- Jorge aprueba pasar de Draft a Accepted.

## 13. Rollback

Dry-run: descartar artefactos temporales.

Si en una fase futura Jorge autoriza escritura en ambiente temporal:

1. Registrar `batch_id` o `source_fingerprint`.
2. Revertir aliases del batch.
3. Revertir datos derivados del batch.
4. Eliminar solo pacientes sin vinculos externos al batch.
5. Bloquear borrado si hay episodios, facturas, consultas, protocolos o aliases ajenos al batch.

## 14. Piloto 20 pacientes

No autorizado todavia.

Plan cuando se autorice:

- 5 pacientes solo demografia.
- 5 pacientes con formularios `eye_mag`.
- 5 pacientes con procedimiento/protocolo.
- 5 pacientes con senales de facturacion/afiliacion.

Todos los ejemplos y fixtures deben ser sinteticos o anonimizados; cero PII.
