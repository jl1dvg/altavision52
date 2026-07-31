# Handoff MedForge Core - P0 core_external_aliases

Estado: especificacion, no codigo.
Clasificacion: deuda P0 del Core.
Separacion obligatoria: no mezclar con importador OpenEMR ni con Patient V1.

## Objetivo

Corregir la restriccion de unicidad de `core_external_aliases` para que refleje su naturaleza polimorfica.

Nueva unicidad minima:

```php
['aliasable_type', 'instance_slug', 'provider', 'external_type', 'external_id']
```

No incluir `aliasable_id` en la restriccion unique.

## Contexto aprobado

- Alta Vision usara instancia clinica y DB independiente.
- `core_patients` no tendra `organization_id` ni `instance_id`.
- `core_patients.id` sera identidad clinica soberana.
- `pid`, `pubpid`, `hc_number`, `form_id` y otros identificadores externos seran aliases.
- Patient V1 debe reutilizar arquitectura Provider, `ProviderIdentityResolver`, `AliasResolution` y `core_external_aliases`.
- No se autoriza identidad paralela para pacientes.

## Archivos probables

- `laravel-app/database/migrations/YYYY_MM_DD_HHMMSS_fix_core_external_aliases_polymorphic_uniqueness.php`.
- `laravel-app/app/Modules/Core/Models/ExternalAlias.php`.
- `laravel-app/app/Modules/Core/Http/Controllers/SedesUiController.php`.
- `laravel-app/app/Modules/Core/Services/ProviderIdentityResolver.php`.
- `laravel-app/app/Modules/Core/Models/AliasResolution.php`.
- `laravel-app/tests/Feature/Core/CoreExternalAliasUniquenessTest.php`.
- `laravel-app/tests/Feature/Core/ProviderIdentityResolverTest.php`.

## Auditoria previa sin PII

Antes de tocar indices, detectar colisiones que bloquearian el nuevo unique:

- mismo `aliasable_type`;
- mismo `instance_slug`;
- mismo `provider`;
- mismo `external_type`;
- mismo `external_id`;
- mas de un `aliasable_id`.

Reporte permitido:

- conteo total de aliases auditados;
- conteo de colisiones bloqueantes;
- `aliasable_type`;
- `instance_slug`;
- `provider`;
- `external_type`;
- hash SHA-256 de `external_id`, no valor crudo;
- cantidad de `aliasable_id` distintos;
- cantidad de filas.

No permitido:

- imprimir `external_id` crudo;
- imprimir nombres, cedulas, telefonos, correos o datos clinicos;
- eliminar, fusionar o reasignar aliases automaticamente.

Comando sugerido:

```bash
php artisan core:external-aliases:audit-uniqueness --no-pii --format=json
```

## Migracion Laravel reversible

`up()`:

- Auditar colisiones bloqueantes.
- Si existen colisiones bloqueantes, lanzar excepcion antes de modificar indices.
- Dropear el unique anterior sobre `instance_slug`, `provider`, `external_type`, `external_id` si existe.
- Crear `core_external_aliases_polymorphic_instance_unique` sobre `aliasable_type`, `instance_slug`, `provider`, `external_type`, `external_id`.

`down()`:

- Dropear `core_external_aliases_polymorphic_instance_unique`.
- Auditar si existen valores compartidos entre tipos que impedirian restaurar el unique anterior.
- Si existen, detener rollback con excepcion.
- Restaurar el unique anterior solo si es representable.

## Comportamiento esperado

- Patient y Sede pueden compartir el mismo `instance_slug`, `provider`, `external_type`, `external_id`.
- Dos Patient no pueden compartir el mismo alias externo dentro de la misma instancia.
- `ProviderIdentityResolver` conserva match deterministico filtrado por `aliasable_type`.
- `AliasResolution` sigue usando la misma tabla y no cambia de contrato.

## Pruebas obligatorias

- Crear Patient y Sede con el mismo valor externo: debe pasar.
- Crear dos Patient con el mismo valor externo: debe fallar por unique.
- Auditoria reporta hashes y conteos sin PII.
- Migracion se detiene si detecta colisiones bloqueantes.
- `ProviderIdentityResolverTest` sigue pasando.
- Prueba de `AliasResolution` confirma compatibilidad.

## Criterios de aceptacion

- PR independiente contra MedForge, clasificado P0 Core.
- Migracion reversible.
- Sin importador OpenEMR.
- Sin fusion automatica.
- Sin PII en logs/reportes/tests.
- Tests verdes para Patient, Sede, ProviderIdentityResolver y AliasResolution.
