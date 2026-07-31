# Orquestacion y siguientes pasos — OpenEMR Alta Vision a MedForge

Fecha local Ecuador: 2026-07-31
Estado general: Patient V1 permanece Draft. No se autoriza todavia migracion masiva ni escritura en produccion.

## Objetivo del programa

Migrar Alta Vision por etapas desde OpenEMR hacia MedForge y, al mismo tiempo, convertir las mejores logicas, formularios y procesos desarrollados en OpenEMR en capacidades nativas de la aplicacion base de MedForge.

El objetivo no es copiar OpenEMR ni crear una dependencia permanente. Cada dominio debe dejar dos resultados:

1. Datos de Alta Vision migrados y reconciliados.
2. Una capacidad reusable incorporada a MedForge Core, al modulo de Oftalmologia o a configuracion por instancia.

## Gobierno

- Jorge: decisiones funcionales, clinicas, tratamiento de datos reales y cambios irreversibles.
- ChatGPT: orquestacion, orden de fases, revision cruzada, actualizacion de Notion y seguimiento de PR/Markdown.
- Codex: auditoria y modificaciones en `altavision52` / OpenEMR, contratos, extractores y reconciliacion del origen.
- Claude Code: auditoria y modificaciones en `MedForge`, arquitectura Laravel, migraciones, servicios y pruebas del destino.

## Equivalencias confirmadas por Jorge

| OpenEMR Alta Vision | MedForge | Regla |
|---|---|---|
| `patient_data.pid` | alias externo OpenEMR asociado a `core_patients.id` | ID tecnico interno del origen; no se reutiliza como PK de MedForge |
| `patient_data.pubpid` | `patient_data.hc_number` | Ambos representan cedula o numero de identificacion |
| `patient_data.lname2` | `patient_data.lname2` | Mapeo directo; existe en ambos sistemas |
| identidad interna MedForge | `core_patients.id` | Identidad soberana y nueva del Core |
| fila demografica legacy | `patient_data.id` | Se conserva como ID tecnico de la tabla legacy, no como identidad universal |

Aunque `pubpid` y `hc_number` tienen la misma semantica funcional, OpenEMR presenta valores vacios y duplicados. Estos casos deben reportarse y resolverse sin fusion automatica.

## Estado verificado

### Alta Vision / OpenEMR

- PR base limpio: #56, Patient V1 Draft contra `master`.
- PR incremental de readiness: #57, limpio y acotado a documentacion.
- `patient_data`: 22.653 pacientes.
- `pid`: completo y unico.
- `pubpid`: equivalente a cedula/identificacion, con vacios y duplicados que requieren politica de conflicto.
- `lname2`: personalizacion real y compatible con MedForge.
- No se extrajo PII ni se realizaron escrituras.

### MedForge

- `core_patients` y `core_external_aliases` ya existen.
- Debe corregirse en un PR P0 independiente la unicidad polimorfica de `core_external_aliases`.
- PR #1065 es solo una revision documental, pero su diff actual esta contaminado: base `main`, 126 commits y 170 archivos. No debe mergearse en ese estado; Claude debe reconstruir el informe en una rama limpia desde `origin/staging`.

## Siguientes pasos inmediatos

### Paso 1 — Corregir y cerrar el contrato Patient V1

Responsable: Codex en `altavision52`.

Actualizar `patient-v1.md` y `patient-v1.schema.json` para que quede explicito:

- `pubpid -> patient_data.hc_number` como cedula/identificacion.
- `lname2 -> patient_data.lname2` por mapeo directo.
- `pid -> core_external_aliases` como alias tecnico de OpenEMR.
- `core_patients.id` como identidad interna soberana.
- `patient_data.id` como ID tecnico legacy.
- Manejo de `pubpid` vacio o duplicado sin merge automatico.

Orden documental recomendado:

1. Revisar y mergear PR #56.
2. Retargetear PR #57 a `master` despues del merge de #56, o mergearlo como PR apilado inmediatamente despues de #56.
3. Mantener Patient V1 como Draft hasta completar los bloqueantes del destino.

### Paso 2 — Limpiar la revision MedForge

Responsable: Claude Code en `MedForge`.

- No continuar sobre PR #1065 tal como esta.
- Crear una rama limpia desde `origin/staging`.
- Llevar unicamente el documento `docs/architecture/openemr-medforge-patient-v1-review-2026-07-31.md` y las correcciones documentales necesarias.
- Abrir un PR documental limpio hacia `staging`.
- Cerrar o marcar #1065 como superseded despues de crear el reemplazo.

### Paso 3 — Corregir aliases P0 en MedForge

Responsable: Claude Code en un PR separado hacia `staging`.

- Auditar colisiones agregadas sin PII.
- Cambiar la unicidad a `aliasable_type + instance_slug + provider + external_type + external_id`.
- No incluir `aliasable_id` en el indice unico.
- Incluir migracion reversible y pruebas para Patient/Sede.
- No mezclar este PR con el importador OpenEMR.

### Paso 4 — Preparar Alta Vision en Control Center

Responsable: Claude Code; ejecucion real requiere autorizacion de Jorge.

Preparar:

- organizacion `alta-vision`;
- instancia `altavision-staging`;
- instancia `altavision-production`;
- base clinica independiente por instancia, aunque pueda compartir VPS.

### Paso 5 — Fixtures sinteticos y piloto tecnico

Responsables: Codex + Claude Code, coordinados por ChatGPT.

- Crear fixtures completamente sinteticos.
- Incluir casos normales, `pubpid` vacio, `pubpid` duplicado, `lname2`, telefono/email invalidos y paciente ya existente.
- Probar validacion del contrato, idempotencia, cola de revision y rollback.
- No usar pacientes reales en esta etapa.

### Paso 6 — Pasar Patient V1 de Draft a Accepted

Condiciones:

- mapping corregido;
- PR P0 de aliases aprobado;
- instancias preparadas en Control Center;
- fixtures sinteticos validados;
- revision final de Jorge.

### Paso 7 — Migracion de Pacientes por lotes

Solo despues de Accepted:

1. dry-run sin escritura;
2. piloto autorizado de 20 registros controlados;
3. reconciliacion origen/destino;
4. lote de 100;
5. lotes incrementales con rollback y metricas;
6. carga delta final cuando MedForge asuma la operacion.

## Patron para enriquecer MedForge en cada etapa

Cada dominio de OpenEMR se clasificara antes de implementarse:

- **MedForge Core:** pacientes, profesionales, sedes, citas, encuentros, diagnosticos, documentos, recetas, auditoria y consentimientos.
- **MedForge Ophthalmology:** agudeza visual, refraccion, tonometria, biomicroscopia, fondo de ojo, lateralidad, planes y formularios oftalmologicos.
- **Configuracion Alta Vision:** catalogos, plantillas, reglas o flujos exclusivos de la clinica.
- **Retirar/no migrar:** codigo obsoleto, duplicado, inseguro o reemplazado por una solucion mejor.

Ciclo obligatorio por dominio:

1. Codex audita datos, codigo y uso real en OpenEMR.
2. ChatGPT define alcance, clasificacion y orden.
3. Claude audita el destino e implementa la capacidad base en MedForge.
4. Se crean contratos y fixtures sinteticos.
5. Se ejecuta piloto pequeno y reconciliacion.
6. Se migra por lotes.
7. La capacidad queda documentada como parte reusable de MedForge.

## Orden de dominios posterior a Pacientes

1. Profesionales, usuarios y sedes.
2. Agenda y citas futuras.
3. Encuentros/consultas e historia clinica longitudinal.
4. Formularios oftalmologicos y logicas desarrolladas en OpenEMR.
5. Diagnosticos, recetas y ordenes.
6. Documentos, imagenes y consentimientos.
7. Procedimientos, cirugias y protocolos.
8. Facturacion y cartera.
9. Sincronizacion incremental, corte y OpenEMR en solo lectura.

## Regla de control

Ninguna fase se considera terminada solo porque un script se ejecuto. Deben quedar verificadas la integridad de datos, la logica funcional, la trazabilidad, la idempotencia, el rollback y la incorporacion reusable en MedForge base.
