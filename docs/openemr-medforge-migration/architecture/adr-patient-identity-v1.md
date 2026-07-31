# ADR propuesta - Identidad clinica Patient V1

Estado: Proposed / Draft. No aceptada por Jorge.

## Contexto

Alta Vision migra desde OpenEMR. OpenEMR usa `patient_data.pid` como identidad operacional y `forms.form_id` como pivote de formularios. MedForge legacy usa `patient_data.hc_number` como llave transversal hacia consultas, procedimientos, protocolos, agenda, facturacion y WhatsApp. MedForge Core ya define `core_patients` y `core_external_aliases`.

La arquitectura MedForge vigente recomienda instancia por clinica, no multi-tenant por fila. Control Center modela `control_center_organizations` y `control_center_instances`.

## Decision propuesta

La identidad clinica soberana es `core_patients.id`.

Toda identidad externa debe registrarse como alias con:

- `organization_id`
- `instance_id`
- `source_system`
- `source_instance`
- `external_type`
- `external_value`

`pid`, `pubpid`, `hc_number`, cedula y `form_id` no son identidad soberana.

## Reglas

- Un alias exacto dentro de una instancia solo puede apuntar a un paciente.
- Duplicados de alias bloquean escritura y abren revision humana.
- Coincidencias demograficas no hacen merge automatico.
- `form_id` pertenece a formulario/episodio legacy; no debe ser alias primario de paciente.
- `hc_number` es alias legacy, aunque hoy sea llave operativa en MedForge.
- Altavision no hereda defaults CIVE/SigCenter.

## Consecuencias

- Puede requerir evolucion de `core_external_aliases`, que hoy usa `instance_slug`, `provider`, `external_type`, `external_id`.
- Se necesita revision Claude Code para definir si se agregan columnas nuevas o si se mapea temporalmente `source_system/source_instance` sobre campos existentes.
- Patient V1 queda Draft hasta verificar base real o dump estructural sin datos.

## Rollback

Todo piloto write autorizado debe tener `batch_id` o `source_fingerprint`. Para revertir:

1. Eliminar aliases del batch.
2. Eliminar perfiles derivados del batch.
3. Eliminar solo pacientes sin vinculos externos al batch.
4. Bloquear borrado si hay episodios, facturas, protocolos, consultas o aliases no pertenecientes al batch.
