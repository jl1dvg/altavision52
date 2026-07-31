# Patient V1 - matriz de riesgos, aceptacion, rollback y piloto

Estado: Draft.

## Riesgos

| Riesgo | Impacto | Mitigacion | Estado |
|---|---|---|---|
| Base real no accesible | No se puede aceptar contrato | Requerir dump estructural sin datos o acceso read-only | Abierto |
| PII en artefactos | Alto legal/clinico | No seleccionar filas; usar solo agregados y fixtures sinteticos | Controlado |
| `pid`, `pubpid`, `hc_number` no coinciden | Duplicados o merge incorrecto | Alias exacto obligatorio; similitud solo genera revision | Abierto |
| `hc_number` ausente en OpenEMR | Mapping incompleto | Verificar columnas reales; no asumir | Abierto |
| CIVE/SigCenter heredado por Altavision | Contaminacion de cliente | `instance_slug` explicito y fail-closed | Controlado en diseno |
| Facturacion mezclada con identidad | Modelo inestable | Separar `patient-v1` de futuro `billing-v1` | Abierto |
| Implementacion sin revision Claude | Riesgo MedForge | Bloquear implementacion hasta informe de impacto | Abierto |

## Criterios de aceptacion para Patient V1

- Estructura real de `patient_data` verificada contra dump o acceso read-only.
- Conteos agregados de calidad de datos disponibles sin PII.
- Columnas personalizadas identificadas.
- Reglas de alias aprobadas por Jorge o por ADR aceptada.
- Revision Claude Code recibida y consolidada.
- `organization_id`, `instance_id`, `source_system`, `source_instance`, `external_type`, `external_value` validados contra MedForge.
- Plan piloto de 20 pacientes usa datos anonimizados/sinteticos.
- Rollback documentado.

## Estrategia de rollback

Dry-run: borrar artefactos temporales.

Write temporal autorizado:

1. Ejecutar en ambiente no productivo.
2. Marcar cada registro con `batch_id` o `source_fingerprint`.
3. Revertir solo el batch.
4. Eliminar aliases antes que pacientes.
5. No borrar pacientes con relaciones fuera del batch.

## Piloto de 20 pacientes anonimizados

Distribucion:

- 5 pacientes solo demografia.
- 5 pacientes con formularios `eye_mag`.
- 5 pacientes con procedimientos/protocolos.
- 5 pacientes con senales de facturacion/afiliacion.

Salida:

- 20 JSON validos contra `patient-v1`.
- 0 PII.
- Reporte de calidad agregado.
- Reporte de conflictos de alias.
- Recomendacion final: Accepted o Draft con bloqueantes.
