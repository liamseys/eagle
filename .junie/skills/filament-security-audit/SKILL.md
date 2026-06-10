---
name: filament-security-audit
description:
  Audit a Filament v5 application for security issues and write a per-finding
  remediation plan. Use when asked to security-audit, security-review, harden,
  or pen-test a Filament panel, resource, page, or Livewire component.
---

# Filament Security Audit

Audit how the application _uses_ Filament v5, not Filament's own source.

> Your output is a specification document. The implementing agent will only see
> your plan, so every finding must name the exact component, namespace, method,
> and docs URL needed to fix it — no guessing.

## How to Scan

**Search-anchored, never file-by-file.** Each catalogue check gives you a
search; run it. Then inspect only the code around each hit. If you open a file
no search pointed you to, stop. Run every search across all source roots (`app/`
and any namespaced roots; Blade under `resources/views`, plus mail /
notification / PDF view roots). For multi-panel apps, first note which
resources/pages belong to which — authorization expectations differ per panel.
**Carve-out:** panel provider classes, `config/`, `.env`, and `composer.json`
are always in scope — open them whenever a check needs to resolve a disk,
default, panel setting, or dependency version.

Check shape tags:

- **`[Site]`** — search finds the vulnerable construct directly. Inspect each
  hit.
- **`[Seed]`** — search finds a seed set (policies, owner FKs, custom Livewire
  components); inspect related code only.
- **`[Conditional]`** — finding only when a precondition holds. Verify **Flag
  if** before reporting.

Highest-yield checks if time-boxed: [A1], [A2], [B1], [C1], [D1].

### Run in parallel with subagents

Every check is an independent search, so this audit parallelises cleanly. If you
can spawn subagents, partition by category (A–E); each returns structured
findings (check ID, location, component, fix) — not prose — and the orchestrator
merges and writes the plan. No subagents? Run sequentially.

## Writing the Plan

A single Markdown document with the sections below. The reader will read every
finding — there are no severity ratings; findings are grouped by check category
(A–E) in §2 so similar issues sit next to each other.

- **Flag only what's actually exploitable in _this_ codebase.** A C2 case whose
  sink is sanitised by the framework is not a finding. The catalogue describes
  what to look for; what _fires_ depends on the conditions in the codebase. When
  in doubt, lean toward `Pass` — noise is the failure mode this skill exists to
  suppress.
- **Consolidate systemic findings.** A check firing across many locations gets
  **one** entry with a list of affected locations, not one per occurrence.
- **`Pass` / `N/A` is not a finding.** Don't raise a "future hardening"
  follow-up for a check whose trigger you just certified absent. A one-line note
  in the Checks Performed row is the maximum; project-wide hardening
  recommendations go in §5.
- **Every real issue gets a `F-NN` ID in §2.** No "asides" / "notes" / "things
  to watch" section — an implementing agent will skip it. If you can describe
  the bug, you can write a numbered entry for it.
- **`Fix:` is one pasteable thing.** Not a menu, not "and similar editors
  elsewhere", not "ask the team". If the right call genuinely depends on a team
  choice, pick the safer default and note the alternative in one line. Enumerate
  every affected location by file:line — "and others" is not actionable.

### 1. Summary

One paragraph + a per-category count (A / B / C / D / E). Count **distinct
findings** (a consolidated systemic finding is one), so totals equal §2 entries.
State which panels / directories you scanned.

### 2. Findings

Grouped by check category (A. Access Control / B. File Uploads & RCE / C. XSS &
Injection / D. Query Scoping & Data Exposure / E. Dependencies). Inside a
category, order by check ID then by location. Required shape:

```

### [F-01] Inline ToggleColumn on `is_admin` bypasses the update policy

Check: A4
Location: app/Filament/Resources/Users/Tables/UsersTable.php:42
Component: Filament\Tables\Columns\ToggleColumn
Docs: https://filamentphp.com/docs/5.x/tables/columns/toggle#authorization

Issue: The `is_admin` ToggleColumn is editable inline. Inline columns don't
run model policies — only `->disabled()`. Any user who can see the row can
toggle admin status via Livewire.

Fix: ->disabled(fn (User $record): bool => ! auth()->user()->can('update', $record))

Verify: Test a non-admin user cannot update the column (see Recommended Tests).
```

Stable ID (`F-NN`), the catalogue check ID, `file:line`, full namespace, docs
URL, issue, pasteable fix, verify.

### 3. Checks Performed

A table of every catalogue check with `Finding` / `Pass` / `N/A`. One-line
reason for each `N/A`.

### 4. Recommended Tests

Tests covering the confirmed findings, using Filament's helpers
(https://filamentphp.com/docs/5.x/testing/overview).

### 5. Optional Hardening Tips

Project-wide configuration knobs that aren't a §2 finding today but reduce
regression risk. Strict rules:

- **Only project-wide configuration.** No file:line. Anything pointing at a
  specific location is a §2 finding instead.
- **Trigger condition required.** Each tip names the verified condition that
  made it relevant (e.g. "5 non-Spatie private FileUpload fields exist").
- **Omit §5 entirely when empty** — don't stub it as "no hardening tips needed."

This is not an "asides" escape hatch — see the §2 rules. Real issues belong in
§2.

### When to ask vs proceed

- **Proceed** by default — this skill produces a plan, not edits.
- **Ask** only when intent is ambiguous _and_ changes the verdict (e.g. a
  resource that may be intentionally open). State your assumption and continue.

# Security Checks Catalogue

Every Filament v5 security notice, grouped by category (A–E). Each entry lists
**Search**, **Flag if**, **Fix**, **Docs**, with **Why** / **Safe when** /
**Exceptions** where useful. Reference:
https://filamentphp.com/docs/5.x/advanced/security

## A. Access Control

### A1. Bulk delete/restore missing the `*Any()` policy guard — `[Seed]` `[Conditional]`

A _missing_ policy is **not** a finding. This is only the narrow inconsistency
where a per-record guard exists but the matching bulk guard does not.

- **Search**: candidate policies —
  `grep -rnE "function (delete|forceDelete|restore)\(" app/Policies` — keeping
  only those whose body does real work (references `$record`, `$user`, `Gate`,
  or `->can(`, not a bare `return true;`). Then confirm a matching bulk action
  exists —
  `grep -rnE "DeleteBulkAction|ForceDeleteBulkAction|RestoreBulkAction" app/Filament`
  (including the default group) — and whether the guard is defined:
  `grep -rnE "function (deleteAny|forceDeleteAny|restoreAny)\(" app/Policies`.
- **Flag if**: the per-record method does real work **and** a matching bulk
  action exists **but** the policy has no corresponding `deleteAny()` /
  `forceDeleteAny()` / `restoreAny()`.
- **Fix**: record-independent check (role/permission gate) → copy the same logic
  into the `*Any()` method. Record-dependent check (ownership) → add
  `->authorizeIndividualRecords('delete')` (resp. `'forceDelete'`, `'restore'`)
  to the bulk action so Filament re-checks per record.
- **Why**: bulk actions authorize the whole batch once against `*Any()`, never
  per-record — a missing `*Any()` fails open. (With the panel's
  `->strictAuthorization()` setting enabled a missing `*Any()` throws instead —
  N/A there.)
- **Docs**:
  - https://filamentphp.com/docs/5.x/resources/deleting-records#authorization
  - https://filamentphp.com/docs/5.x/actions/delete#improving-the-performance-of-delete-bulk-actions

### A2. Import bypasses the `create()` / `update()` policy — `[Seed]` `[Conditional]`

Anchored on inconsistency (like [A1]): a model with a meaningful `create()` /
`update()` policy while an importer writes records with no equivalent
authorization. A _missing_ policy is not the trigger.

- **Search**: `grep -rn "ImportAction::make" app` and open the referenced
  importer. For completeness, `grep "extends Importer"` catches imports run
  outside `ImportAction` (commands, queued re-runs).
- **Flag if**: the model's policy has a real `create()` / `update()` **but** the
  importer contains no `can(` / `Gate::` / `authorize(` / `abort` check in any
  of its overridable hooks (`resolveRecord()`, `beforeValidate`, `beforeFill`,
  `beforeSave`, `beforeCreate`, `beforeUpdate`) — authorizing in any one is
  safe.
- **Fix**: record-independent → copy the check into the hook; record-dependent
  (update) → `abort_unless(auth()->user()->can('update', $this->record), 403)`;
  new records (create) →
  `abort_unless(auth()->user()->can('create', static::getModel()), 403)` in
  `beforeCreate()` (the record is filled-but-unsaved there).
- **Why**: `ImportAction` resolves, fills, and saves each CSV row without
  consulting Laravel policies.
- **Docs**:
  https://filamentphp.com/docs/5.x/actions/import#per-record-authorization

### A3. Overridden `can*()` methods no longer invoked (v4+) — `[Site]` `[Conditional]`

- **Search**:
  `grep -rnE "function can(Create|Edit|View|ViewAny|Delete|DeleteAny|ForceDelete|ForceDeleteAny|Restore|RestoreAny|Reorder|Replicate|Attach|Detach|DetachAny|Associate|Dissociate|DissociateAny)\(" app/Filament`.
- **Flag if**: an override shows authorization intent in its body — references
  `auth()`, `$user`, `Gate`, `->can(`, or `abort` — and that rule is not also
  enforced by a policy. (Skip static returns with no such signal.)
- **Fix**: move the logic into the model policy, or override the matching
  `get*AuthorizationResponse()` method (which must return an
  `Illuminate\Auth\Access\Response` — `Response::allow()` / `Response::deny()` —
  not a bool).
- **Why**: in v4+ `can*()` still gates page access, navigation, and global
  search, but record/bulk **actions** and relation managers authorize via
  `get*AuthorizationResponse()` directly — so the page looks gated while the
  action leaks.
- **Docs**:
  https://filamentphp.com/docs/5.x/upgrade-guide#overriding-the-can-authorization-methods-on-a-resource-relationmanager-or-managerelatedrecords-class

### A4. Inline editable columns bypass the `update()` policy — `[Site]` `[Conditional]`

Anchored on inconsistency: a model whose policy does real `update()` work while
an inline column saves to it without an equivalent guard.

- **Search**: `grep -rnE "(Select|Toggle|TextInput|Checkbox)Column::make" app`
  across all source roots. If tables build columns from custom subclasses, also
  `grep -rnE "extends (Toggle|Select|TextInput|Checkbox)Column" app`.
- **Flag if**: the model policy has a meaningful `update()` **and** the column
  has no `->disabled()` closure carrying an auth check. Static
  `->disabled(true)` and `->rules(...)` (validation, not authorization) do not
  count.
- **Safe when**: no meaningful `update()` policy (mark `N/A`), or the column
  carries its own auth inside `->updateStateUsing()` / `->beforeStateUpdated()`.
- **Fix**: record-independent →
  `->disabled(fn (): bool => ! auth()->user()->can('update_posts'))`;
  record-dependent →
  `->disabled(fn ($record): bool => ! auth()->user()->can('update', $record))`;
  or move the field to a policy-gated Edit page / modal action.
- **Why**: these columns save via a Livewire request that checks only
  `->disabled()` (and field validation) — never the `update()` policy — so any
  user who can see the row can write the value.
- **Docs**:
  - https://filamentphp.com/docs/5.x/tables/columns/toggle#authorization
  - https://filamentphp.com/docs/5.x/tables/columns/text-input#authorization
  - https://filamentphp.com/docs/5.x/tables/columns/select#authorization
  - https://filamentphp.com/docs/5.x/tables/columns/checkbox#authorization

### A5. Livewire upload RPC on components without an upload field — `[Seed]` `[Conditional]`

- **Search**:
  `grep -rlE "InteractsWith(Schemas|Forms|Infolists|Actions|Table)" app`,
  excluding classes that extend Filament's `Resource` / `Page` /
  `RelationManager`. Then confirm each hit composes `InteractsWithSchemas`
  directly, or via `InteractsWithForms` (which itself composes it) — only those
  components expose the upload RPC. A class that only uses `InteractsWithTable`
  / `InteractsWithActions` does not, so it is not a target.
- **Flag if**: a custom component is reachable by untrusted users (check route
  middleware, or whether it's rendered on a public Blade view) and lacks
  `RestrictsFileUploadsToSchemaComponents`. Chief cases: unauthenticated pages,
  or components whose schema has no upload field.
- **Fix**: add
  `Filament\Schemas\Concerns\RestrictsFileUploadsToSchemaComponents` to the
  component class (which must implement `HasSchemas` / `HasForms`); it 403s
  uploads whose target isn't an upload field in the component's schema.
- **Why**: `InteractsWithSchemas` composes Livewire's `WithFileUploads`,
  exposing `_startUpload` / `_finishUpload` everywhere. Panel resources/pages
  re-authorize every request, so the trait isn't needed there.
- **Docs**:
  https://filamentphp.com/docs/5.x/advanced/security#restricting-livewire-file-uploads-to-schema-components

### A6. Custom Livewire: work runs before authorization — `[Seed]` `[Conditional]`

- **Search**: the vulnerable construct is a lifecycle hook that runs before the
  auth check, so anchor on the hooks themselves —
  `grep -rnE "function (boot|mount|hydrate)" app` (catches `boot()`,
  `boot{Trait}()`, the `mount()` body, and per-property `hydrate{Prop}()`) — in
  custom Filament `Page` classes and standalone Livewire components. (A resource
  page is only a target if it adds sensitive work _above_ its own
  `authorizeAccess()` call.)
- **Flag if**: a `boot()` / `boot{Trait}()` body, a custom page's `mount()`
  body, or a bare `hydrate()` hook performs sensitive side effects (DB writes,
  event dispatch, external calls) not preceded by an authorization check. (A
  per-property `hydrate{Prop}()` hook runs just _after_ the auth check — flag it
  only as defence-in-depth.)
- **Fix**: do the work _after_ authorization has fired — in the `mount()` body
  below an explicit `$this->authorizeAccess()` (resource pages) /
  `abort_unless(static::canAccess(), 403)` (custom pages) call, or in a
  `wire:click` action method (always post-authorization). Avoid sensitive work
  in `boot()` or per-property hydrate hooks.
- **Why**: Filament wires page authorization into Livewire _trait_ hooks —
  `CanAuthorizeAccess` on custom pages, `mountCanAuthorizeResourceAccess()` /
  `hydrateCanAuthorizeResourceAccess()` on resource pages — which fire _after_
  the component's own `boot()` / `mount()` / bare `hydrate()` body. So side
  effects in those hooks run even when the request is ultimately aborted 403.
  Resource pages call `authorizeAccess()` explicitly inside `mount()`, so only
  code _above_ that line is exposed.
- **Docs**:
  https://filamentphp.com/docs/5.x/advanced/security#authorization-and-the-livewire-request-lifecycle

## B. File Uploads & RCE

### B1. Path tampering on shared disks (`FileUpload` + `RichEditor`) — `[Seed]` `[Conditional]`

On a shared private disk, every unprotected writer of either type is an
exfiltration primitive — a user can tamper their own field to read content other
users uploaded through other fields on the same disk. Provider-backed fields
(Spatie `FileUpload` subclass, provider-backed `RichEditor`) are safe
**writers**, but their content remains a potential **target**: a tampered
non-provider field can read provider-backed content if its path is known.

- **Search**:
  1. **Group all upload-storing fields by resolved disk** —
     `grep -rnE "(FileUpload|SpatieMediaLibraryFileUpload)::make" app` and
     `grep -rn "RichEditor::make" app`, resolving each field's disk via
     `->disk(...)` or `->fileAttachmentsDisk(...)` → panel default →
     `config('filament.default_filesystem_disk')` → `FILESYSTEM_DISK`. Drop:
     - Public / web-served disks (already addressable — no escalation).
     - Disks single-user / single-tenant **by infrastructure** — Flysystem
       `root` bound per tenant at framework level: static in
       `config/filesystems.php`, dynamic via
       `Storage::set('uploads', ['root' => "/tenants/{$tenantId}"])` in a
       service provider / middleware, or a tenancy package (Spatie
       multi-tenancy, Stancl/Tenancy). App-layer prefixing on a shared root does
       NOT count — tampering bypasses string prefixes.

  2. **Find disclosure targets per disk** — any file on the disk root worth
     exfiltrating, regardless of which mechanism uploaded it:
     - **Sensitive content** in FileUpload / SpatieMediaLibraryFileUpload fields
       or RichEditor image attachments. Judgement; ask the user when unclear.
       Reliable name signals (apply to the field's or editor's hosting model):
       `Medical*`, `Health*`, `Patient*`, `Tax*`, `Bank*`, `Invoice*`,
       `Statement*`, `Identity*`, `Passport*`, `Credential*`, `Token*`,
       `Secret*`. Generic names like `Document` / `Attachment` / `File` /
       `Upload` are **not** signals on their own — context determines
       sensitivity (check `composer.json` / `.env` for HIPAA / PCI / PII hints,
       and the field's actual domain use).
     - **Enumerable filenames** —
       `grep -rn "preserveFilenames\|getUploadedFileNameForStorageUsing"` across
       both field types. Spatie / UUID-scoped randomness is the safe case;
       preserved or deterministic names are targets.
     - **Non-Filament content** on the disk root — generated PDFs, queued
       exports, log dumps written by other code paths.

  3. **Find unprotected writers per disk** — two sub-categories:
     - **FileUpload writers**: non-Spatie `FileUpload` fields without
       `preventFilePathTampering(true)` (per-field, or via global
       `FileUpload::configureUsing(...)` default).

     - **RichEditor writers**: editors satisfying all three of:
       - **Accepts attachments** — default toolbar includes `attachFiles`; a
         custom toolbar (`->toolbarButtons([...])`) that omits it disables
         attachment uploads, so the editor can't insert `<img data-id>` nodes.
         Skip.
       - **No UUID-scoped provider** — trace the editor to its hosting model and
         check `registerRichContent(..., ...)->fileAttachmentProvider(...)` in
         the model file (`grep -rn "fileAttachmentProvider(" app/Models`
         enumerates registered providers). Spatie's `MediaLibrary` or any custom
         provider that re-validates ownership protects the editor. **Action /
         page schemas with no model backing have nowhere to register a
         provider** — always unprotected.
       - **No tampering protection** — no
         `->preventFileAttachmentPathTampering(true)` on the field, and no
         global `RichEditor::configureUsing(...)` default.

  4. **Gradient or audience check** (per disk, cheap heuristic) — either flags
     an unprotected writer:
     - **Gradient** — any target's gating policy (`view` / `download` /
       equivalent) references `$record` or `$user` _in the method body_
       (ownership scoping creates per-record asymmetry). Read the body; don't
       infer from the signature alone — `view(User $user, Article $record)` may
       never touch `$record`.
     - **Audience** — the rendered field output reaches viewers beyond the
       writer's access scope: an avatar shown on a public profile page or in a
       staff list, an editor body rendered into a public article or into a
       notification email. Even with flat permissions, a publicly rendered
       tampered preview or `<img>` fetches the file without re-authenticating
       the viewer.

  5. **Identify legitimate fill sources per flagged writer** — for each writer
     surviving steps 3 and 4, find every place its value can be set to a path
     that did NOT come from a fresh upload or the record's original. These
     become **mandatory** `allowFilePathUsing:` exclusions in the fix — applying
     the global default without them breaks those workflows. Sources to inspect:
     - **Field defaults** — `->default(...)` setting a path (FileUpload) or HTML
       containing `<img data-id="...">` (RichEditor).
     - **Form fill hooks** — `mutateFormDataBeforeFill()` (page),
       `mutateRecordDataUsing()` (modal action), explicit `fillForm()` /
       `$this->form->fill(...)` calls.
     - **Action fills** — `Action::make(...)->action(...)` closures that call
       `$set('<field>', ...)` or otherwise write the field from a template /
       another record. Grep `->set('<field>'` in panels/pages hosting the
       writer.
     - **Reactive / live updates** — `->afterStateUpdated(...)` or `->live()`
       callbacks writing to the field from elsewhere.

     Encode each allowed pattern in an `allowFilePathUsing:` closure (e.g.
     `str_starts_with($file, 'templates/')` for a template directory; a
     membership check on a specific Spatie media collection used by templates).

- **Flag if**: a disk has targets, unprotected writers (of either type), and a
  gradient or broader audience. One §2 entry per disk, listing every unprotected
  writer and the targets they could reach.

- **Safe when**: no targets; no unprotected writers; no gradient AND no broader
  audience.

- **Fix** (one §2 entry per disk):
  1. Add the relevant global default(s) —
     `FileUpload::configureUsing(fn (FileUpload $component) => $component->preventFilePathTampering())`
     and/or
     `RichEditor::configureUsing(fn (RichEditor $component) => $component->preventFileAttachmentPathTampering())`.
  2. For **every** fill source from step 5, add a per-field exclusion:
     `->preventFilePathTampering(allowFilePathUsing: fn (string $file): bool => str_starts_with($file, 'templates/'))`
     (or its RichEditor equivalent). Enumerate each with file:line and the
     specific allowed pattern.

  **Step 2 is mandatory when step 5 found fill sources** — applying the global
  default alone will break fill-from-template / copy-from-another-record /
  default-attachment workflows in production. Alternative when only a small
  subset of fields is affected: apply the per-field method (with the exclusion
  in the same call) instead of registering a global default.

- **§5 tip**: if a disk has any non-provider writer and the corresponding global
  default is missing
  (`grep -rn "preventFilePathTampering\|preventFileAttachmentPathTampering" app/Providers`),
  the missing default(s) belong in §5 — defends against a future field creating
  the gap, even when no §2 finding fires today.

- **Why**: two mechanisms with the same flaw — both ask the disk for whatever
  path the client supplied.
  - **FileUpload**: Livewire state holds a client-controlled path. Preview /
    download URL methods read state on every render — tampering redirects them
    to any file under the disk root, persistence not required (so
    `storeFiles(false)` is **not** an exemption: the state is still there and
    the URL methods still read it). `->preventFilePathTampering()` validates
    against the record's original value or a fresh upload; off by default.
  - **RichEditor**: image attachment `<img data-id="...">` is client-controlled.
    The editor rewrites each `data-id` into an `<img src>` URL at render time,
    signing whatever path it contains without checking ownership — tampering
    redirects the rendered image to any file under the disk root.
    `->preventFileAttachmentPathTampering()` validates the `data-id`; off by
    default. UUID-scoped attachment providers (Spatie's
    `fileAttachmentProvider(MediaLibrary)`, or any custom provider that
    re-validates ownership) protect by rejecting non-owned paths.

- **Docs**:
  - https://filamentphp.com/docs/5.x/forms/file-upload#authorizing-existing-file-paths
  - https://filamentphp.com/docs/5.x/forms/rich-editor#securing-file-attachment-ids

### B2. Upload field accepts any file type — `[Site]` `[Conditional]`

Without an explicit accepted-type allowlist, `FileUpload` accepts any file. Add
a per-field restriction to every upload — `->acceptedFileTypes([...])`, or the
shortcuts `->image()` / `->avatar()` — so renamed `.php` uploads (whose body is
plain PHP text) are detected by content-sniffing and rejected.

- **Search**: `grep -rnE "(FileUpload|SpatieMediaLibraryFileUpload)::make" app`.
  For each field, check whether `->acceptedFileTypes(`, `->image(`, or
  `->avatar(` is set.

- **Flag if**: a field has no type restriction. An overly-broad list like
  `application/*` is also a finding — the `mimetypes` rule is an _allowlist_
  matched against the file's sniffed MIME, so broad wildcards admit dangerous
  types (`application/x-php`, etc.).

- **Safe when**: the field already restricts via `->image()` / `->avatar()` /
  `->acceptedFileTypes([...])`.

- **Fix** (one §2 entry listing every unrestricted field): add
  `->acceptedFileTypes([...])` per field with the appropriate type list, or use
  `->image()` / `->avatar()` when applicable. Enumerate every affected field
  with file:line.

- **Why**: `FileUpload` accepts any type by default. `->acceptedFileTypes(...)`
  activates Laravel's `mimetypes` rule, which content-sniffs the uploaded file
  via finfo — the client-supplied Content-Type header is ignored and the
  filename extension is never validated. A renamed `.php` file (raw PHP text)
  sniffs as `text/x-php` and fails an `image/*` allowlist. A **polyglot** file
  (valid image magic bytes + embedded PHP) sniffs as an image and passes — it is
  stopped only when paired with [B3] (random storage filenames keep the `.php`
  extension off disk). Real-world impact tracks the field's resolved disk
  (`->disk(...)` → panel default → `config('filament.default_filesystem_disk')`
  → `FILESYSTEM_DISK`):
  - **Web-served disk** (`public` with `storage:link`, or anything Apache /
    Nginx executes from): unrestricted upload → renamed `.php` lands on disk
    with an executable extension → RCE. (B2 alone defeats the renamed-PHP
    attack; [B3] closes the polyglot path.)
  - **Non-served disk** (`s3` / `gcs` / private cloud storage — the production
    default for most Filament apps): no execution path; missing restriction is
    hygiene only, not an exploit. Still worth flagging — a future field added to
    a different disk inherits the unrestricted pattern.

- **Docs**:
  https://filamentphp.com/docs/5.x/forms/file-upload#file-type-validation

### B3. User-controlled file names → remote code execution — `[Site]` `[Conditional]`

- **Search**:
  `grep -rn "preserveFilenames\|getUploadedFileNameForStorageUsing" app`.
- **Flag if**: used together with a `local`/`public` disk — resolve the disk in
  order: the field's `->disk(...)`, then the panel's
  `->defaultFilesystemDisk()`, then `config('filament.default_filesystem_disk')`
  (defaults to `FILESYSTEM_DISK` → `local`). `->storeFileNamesIn(` is the safe
  pattern.
- **Safe when**: the field targets a non-served disk (e.g. `->disk('s3')`), or
  uses `->storeFileNamesIn(` → mark `Pass`. A `local`/`public` disk not actually
  web-served has no execution path either; confirm HTTP reachability before
  flagging. Filename-collision concerns on a non-public disk belong under [B1],
  not here.
- **Fix**: keep random storage names; store the original with
  `->storeFileNamesIn('column')` instead of preserving it on disk.
- **Why**: preserving the client filename keeps a `.php` extension on disk;
  [B2]'s `mimetypes` rule content-sniffs the body but never checks the
  extension, so a **polyglot** upload (image magic bytes + embedded PHP) passes
  an `image/*` allowlist while landing as `something.php` — executable code on a
  PHP-served disk → RCE. Both halves of the chain are needed: B2 alone stops the
  renamed-only `.php`; B3 alone keeps the extension dangerous; closing one
  closes the gap.
- **Docs**:
  https://filamentphp.com/docs/5.x/forms/file-upload#security-implications-of-controlling-file-names

## C. XSS & Injection

Every C-check has two halves — **source** (the interpolated value) and **sink**
(the renderer). Verify both before flagging:

- **Source must be user-controllable.** Trace each interpolated variable one hop
  to its assignment and name the origin in the §2 entry. An enum label
  (`$preset->getLabel()`), a hardcoded map, or `__('...')` is not user input →
  `Pass`.
- **Sink must render raw.** Several Filament paths sanitise downstream
  (`Notification` title/body via `str(...)->sanitizeHtml()`; `->html()` on
  columns/entries; `RichContentRenderer::toHtml()`). Others don't (action
  `modalDescription`, `TextEntry::html()` fed a pre-built `Htmlable`, raw
  `{!! !!}` in Blade, mail/notification views). Sanitised sink → `Pass`.

### C1. Unsanitized rich-editor / markdown output in Blade — `[Seed]` `[Conditional]`

- **Search**: two steps. First list the editor-backed attribute names —
  `grep -rnoE "(RichEditor|MarkdownEditor)::make\(\s*[\"'][^\"']+[\"']\)" app`
  (both quote styles). Then grep for raw echoes —
  `grep -rnE "\{!!" resources/views` plus any mail / notification / PDF view
  roots (e.g. `resources/views/mail`, `resources/views/notifications`) — and
  match them against those names. (Aliased access —
  `$body = $record->content; {!! $body !!}` — slips this heuristic; spot-check.)
- **Flag if**: a `{!! !!}` echoes one of those editor-backed attributes raw
  (without `->sanitizeHtml()`).
- **Fix**: `{!! str($record->content)->sanitizeHtml() !!}` (Markdown:
  `{!! str($record->content)->markdown()->sanitizeHtml() !!}`). If the editor
  uses `->json()`, render with
  `Filament\Forms\Components\RichEditor\RichContentRenderer::make($record->content)->toHtml()`
  (it sanitizes) — `sanitizeHtml()` on raw JSON renders nothing.
- **Why**: editor content is raw user HTML. Filament's own renderers
  auto-sanitise, so only your own raw echoes are at risk.
- **Docs**:
  - https://filamentphp.com/docs/5.x/forms/rich-editor#security
  - https://filamentphp.com/docs/5.x/forms/markdown-editor#security

### C2. Raw HTML bypasses the sanitizer (`HtmlString` / `view()`) — `[Site]` `[Conditional]`

- **Search**:
  `grep -rnE "new HtmlString\(|->toHtmlString\(\)" app resources/views`, plus
  `formatStateUsing(` / `->state(` / `getStateUsing(` returning a `view()` or
  `HtmlString`, plus `TextEntry|TextColumn::make(...)->html(` where the state is
  built upstream from an `HtmlString` interpolation. `->html()` is safe only
  when the upstream state-builder isn't injecting unescaped data — if it is, the
  finding sits at the interpolation site, not the `->html()` call.
- **Flag if**: user-controlled data is interpolated **unescaped** into the raw
  HTML _and_ the sink renders raw (see C-category intro). Static markup, or
  output where every dynamic value is `e()`'d, is N/A.
- **Sink classification**:
  - `Notification::title/body` sanitises downstream → **no finding** (residual
    risk is broken HTML from attributes like `O'Brien` in an `href` — surface as
    a §5 escape-interpolated-values tip if the pattern is widespread).
  - Action `modalDescription/Heading`, `TextEntry/TextColumn::html()` fed a
    pre-built `Htmlable`, raw `{!! !!}` in a Blade/mail/notification view — no
    downstream sanitiser → **finding**.
  - A custom view that calls `->sanitizeHtml()` or
    `RichContentRenderer::toHtml()` on the value before echoing → **Pass**.
- **Fix**: prefer `->html()` (when its state isn't already pre-built raw HTML);
  otherwise `e()` every dynamic value before wrapping `HtmlString`. Symfony's
  `HtmlSanitizer` default permits inline `style` — configure a stricter
  sanitizer for fully untrusted content.
- **Docs**:
  - https://filamentphp.com/docs/5.x/tables/columns/text#rendering-raw-html-without-sanitization
  - https://filamentphp.com/docs/5.x/infolists/text-entry#rendering-raw-html-without-sanitization
  - https://filamentphp.com/docs/5.x/schemas/primes#text-component

### C3. Unsafe URL schemes in `url()` — `[Site]` `[Conditional]`

- **Search**: `grep -rnE "->(url|recordUrl)\(" app` across columns, entries,
  actions, notification actions, mention providers, and `recordUrl()`. Narrow to
  the risky shape (closure or raw attribute, not `route(...)`):
  `grep -rnE "->(url|recordUrl)\(\s*fn|->(url|recordUrl)\([^)]*\\\$record->" app`.
- **Flag if**: any part of the URL derives from user-controlled data (e.g. a
  closure returning a raw model attribute). `route(...)` URLs are safe.
  Input-side validation (`->url()` / `->email()`) on the form field is **not** a
  `Pass` — the stored value can still carry `javascript:` / `data:` (via import,
  seeder, direct write) and is re-emitted unsanitised.
- **Fix**: wrap in `Str::sanitizeUrl($value)` (a Filament macro on
  `Illuminate\Support\Str`, also `str($value)->sanitizeUrl()`; returns the value
  only for schemeless or `http`/`https` URLs, else `null`); pass extra schemes
  via `allowedSchemes:`.
- **Why**: a `javascript:` or `data:` value renders into an `<a href>` and
  executes on click → XSS.
- **Docs**:
  https://filamentphp.com/docs/5.x/advanced/security#validating-user-input

### C4. Unescaped HTML in option labels (`allowHtml` / `allowOptionsHtml`) — `[Site]` `[Conditional]`

- **Search**: `grep -rnE "->(allowHtml|allowOptionsHtml)\(" app` (the trailing
  `(` avoids matching `allowHtmlValidationMessages`, which is [C6]; also covers
  `CheckboxList` / `MorphToSelect`, which share the `allowHtml` flag).
- **Flag if**: the option labels derive from user/DB data (a relationship title,
  a user-entered name). Static developer-authored labels are N/A.
- **Fix**: remove the flag if labels needn't be HTML; otherwise escape any
  dynamic value with `e()` before it reaches the label.
- **Docs**:
  - https://filamentphp.com/docs/5.x/forms/select#allowing-html-in-the-option-labels
  - https://filamentphp.com/docs/5.x/tables/columns/select#allowing-html-in-the-option-labels

### C5. Unescaped `extraAttributes()` values — `[Site]` `[Conditional]`

- **Search**: `grep -rnE "extra[A-Za-z]*Attributes\(" app`.
- **Flag if**: attribute names/values are built from user-controlled data.
  Static arrays (class lists, Alpine/Livewire directives) are N/A.
- **Fix**: pass only trusted/validated data; if a value must be dynamic, escape
  it with `e($value)` before adding it to the attribute array, and never build
  attribute _names_ from user input.
- **Why**: `extra*Attributes()` render values into HTML without escaping by
  design (to allow Alpine/Livewire directives), so user data can break out of
  the attribute.
- **Docs**:
  https://filamentphp.com/docs/5.x/advanced/security#validating-user-input

### C6. Unescaped validation messages (`allowHtmlValidationMessages`) — `[Site]` `[Conditional]`

- **Search**: `grep -rn "allowHtmlValidationMessages" app`.
- **Flag if**: a message interpolates user-controlled data, uses a Laravel
  placeholder that echoes input (`:input`, `:value`), or relies on an HTML /
  user-derived field label (`:attribute`). Developer-authored and
  translation-file messages with no such interpolation are N/A.
- **Fix**: ensure no message interpolates unescaped user data, or remove the
  call.
- **Docs**:
  https://filamentphp.com/docs/5.x/forms/validation#allowing-html-in-validation-messages

### C7. User input in client-side JS expressions — `[Site]` `[Conditional]`

- **Search**:
  `grep -rnE "hiddenJs\(|visibleJs\(|afterStateUpdatedJs\(|actionJs\(|alpineClickHandler\(|JsContent::make\(|->js\(\)" app`.
- **Flag if**: user input is spliced into the JS string via PHP
  interpolation/concatenation. Runtime reads via `$state` / `$get()` are safe.
- **Fix**: use `$get()` / `$state` for dynamic values; never interpolate user
  input PHP-side.
- **Why**: these strings are `eval()`'d client-side, so PHP-side interpolation
  of user data → XSS.
- **Docs**:
  - https://filamentphp.com/docs/5.x/forms/overview#hiding-a-field-using-javascript
  - https://filamentphp.com/docs/5.x/actions/overview#running-javascript-when-an-action-is-clicked

## D. Query Scoping, Data Exposure & Multi-Tenancy

### D1. List/widget query ignores an ownership rule enforced elsewhere — `[Seed]` `[Conditional]`

Anchored on inconsistency, not "scope everything": flag a query that returns
records a per-user ownership rule — visible elsewhere — should have excluded.
(Tenant scoping is [D3].) A scope that is **registered but logically broken**
counts as a D1 finding too — applying a broken scope is the same failure mode as
not applying one.

- **Search**: seed = models with an owner FK
  (`grep -rnE "(user|author|owner|account|customer)_id|created_by" database/migrations app/Models`)
  or a record-dependent `view()` policy
  (`grep -rnE "function view\(" app/Policies` — body references `$record`). For
  each, inspect query sites —
  `grep -rnE "getEloquentQuery|modifyQueryUsing|getTableQuery|getStats|getData" app/Filament`
  — and registered scopes (`#[ScopedBy(...)]`, `addGlobalScope` in `booted()`,
  classes under `app/Models/Scopes`, `app-modules/*/src/Models/Scopes`).
- **Flag if**: (a) the query doesn't apply the ownership scope the policy/FK
  implies, or (b) a query-customisation site (`getEloquentQuery()` override,
  `->modifyQueryUsing(...)`, a filter `query(...)` callback) calls a top-level
  `->where(...)->orWhere(...)` not wrapped in `->where(function ($q) { ... })` —
  Filament appends search/filter constraints _after_ your callback, so the
  top-level `orWhere` escapes the surrounding `AND` group and leaks rows.
  (Registered global scopes do **not** need this wrap: Laravel auto-groups
  `or`-containing scope constraints into a nested `where`.)
- **Fix**: missing-scope → `->modifyQueryUsing(...)`, override
  `getEloquentQuery()`, or add a global scope, and apply the same constraint in
  widgets. Unwrapped-OR → wrap the OR pair in
  `->where(function ($q) { $q->where(...)->orWhere(...); })` at the
  customisation site.
- **Filter `options()` is not an access boundary** — separate search:
  `grep -rnE "SelectFilter::make|SelectConstraint::make" app` and inspect each
  `->options(...)` callback. **Flag if** the option list is narrowed per
  user/role (`auth()` / `$user` / `Gate` / `->can(` / role check inside the
  closure). The submitted value is **not** validated against the returned list
  before hitting `whereIn` / `where`, so a tampered request reaches the "hidden"
  rows. **Fix**: keep the full option list and enforce access in the query
  (above), or wrap the filter in `->visible(...)` and gate the underlying query.
- **Docs**: https://filamentphp.com/docs/5.x/advanced/security#scoping-queries

### D2. Sensitive model attributes exposed to JavaScript — `[Seed]` `[Conditional]`

- **Search**:
  `grep -rnE "(token|secret|password|api[_]?key|two_factor|ssn|tax_id|bank|iban|card_number|private_key|salary)" app/Models database/migrations`,
  plus any sensitive domain column and `$appends` accessors. Restrict to models
  edited via a Filament Edit/View page or modal `EditAction`/`ViewAction`.
- **Flag if**: the attribute isn't in `$hidden`, isn't excluded by a `$visible`
  whitelist, and isn't stripped in `mutateFormDataBeforeFill()`.
- **Fix**: add the column to `$hidden` (covers every path), or `unset()` it in
  `mutateFormDataBeforeFill(array $data): array`. For modal
  `EditAction`/`ViewAction`, the scrub is `->mutateRecordDataUsing(...)`.
- **Why**: Filament exposes all non-`$hidden` attributes to JavaScript via
  Livewire model binding on Edit/View pages. This is _exposure_, not
  mass-assignment — only attributes with a form field are editable.
- **Docs**:
  https://filamentphp.com/docs/5.x/resources/overview#protecting-model-attributes

### D3. Models/queries not auto-scoped to the tenant — `[Seed]` `[Conditional]`

- **Search**: across all source roots (unscoped queries hide in
  jobs/commands/observers) —
  1. `grep -rn "withoutGlobalScopes(" app` — empty-arg calls drop tenancy too.
  2. Enumerate tenant-owned models by their FK
     (`grep -rnE "team_id|organization_id|tenant_id|company_id" database/migrations app/Models`)
     and any `BelongsToTenant`-style trait. For each, confirm it's either
     exposed through a tenant-panel resource (auto-scoped) or explicitly scoped
     elsewhere. Also `grep -rnE "saveQuietly\(|withoutEvents\(|unguarded\(" app`
     for muted creation events.
- **Flag if**: a tenant-owned model has no resource and no explicit scope, is
  queried before tenant identification (early middleware/providers) or outside
  the panel, or `withoutGlobalScopes()` is called with no arguments.
- **Fix** — three mechanisms depending on context:
  1. **Add a resource** for the model (simplest — resource queries are
     auto-scoped via the panel).
  2. **Register a model-level global scope** that filters by
     `Filament::getTenant()`. Pair with
     `tenantMiddleware([...], isPersistent: true)` so the tenant is
     re-identified on Livewire AJAX requests (which bypass panel route
     middleware); non-panel HTTP routes need the panel's tenant middleware
     applied to them directly, and queue jobs need
     `Filament::setTenant($tenant)` at the job's entry — persistent middleware
     doesn't run on workers.
  3. **Use a `creating` model listener** to populate the tenant FK on save
     (covers writes without needing a query scope).

  To drop a single scope without losing tenancy, use
  `withoutGlobalScope(filament()->getTenancyScopeName())`. Never bare-arg
  `withoutGlobalScopes()`.

- **Why**: automatic scoping applies only to models with a resource, only inside
  the panel, only after tenant identification.
- **Docs**: https://filamentphp.com/docs/5.x/users/tenancy#tenancy-security

### D4. Over-permissive tenant-access methods — `[Site]` `[Conditional]`

- **Search**:
  `grep -rnE "function (canAccessTenant|getTenants|canAccessPanel)\(" app`
  (across all source roots — may live on a trait or a modular User model).
- **Flag if**: `canAccessTenant()` returns `true` unconditionally,
  `getTenants()` returns every tenant (`Team::all()`), or `canAccessPanel()`
  returns `true` for everyone on a panel with open registration. **Safe when**
  each gates on real membership. A permissive `getTenants()` alone (with
  `canAccessTenant()` still gated) only discloses tenant names in the switcher —
  verify and note in the §2 Issue paragraph; access is re-checked at
  identification.
- **Fix**: gate on membership — `canAccessTenant`:
  `return $this->teams()->whereKey($tenant)->exists();`; `getTenants`:
  `return $this->teams;`; `canAccessPanel`: a role/flag/email-domain check.
- **Why**: these methods are the front door to a tenant. The impact is latent
  while the panel has no tenant-scoped resources, but becomes a direct
  cross-tenant hole the moment one is added — flag it regardless.
- **Docs**: https://filamentphp.com/docs/5.x/users/tenancy#tenancy-security

### D5. `unique` / `exists` validation ignores the tenancy scope — `[Site]` `[Conditional]`

- **Search**: in tenant-enabled panels (`$isScopedToTenant !== false`),
  `grep -rnE "->(unique|exists)\(" app/Filament` on resource form fields; also
  `grep -rnE "['\"](unique|exists):" app/Filament` to catch the string-rule
  forms (`->rules(['unique:...'])`, `Rule::unique(...)`), which bypass scopes
  identically.
- **Flag if**: a tenant-scoped resource uses `unique()` / `exists()` validation.
- **Fix**: use `->scopedUnique()` / `->scopedExists()` (no-arg defaults to the
  component's model and field name; pass `model:` / `column:` only for a
  non-default table).
- **Why**: Laravel's `unique`/`exists` query the DB directly without global
  scopes, so cross-tenant data influences validation. Unscoped `exists` enables
  cross-tenant reference binding; unscoped `unique` surfaces as false collisions
  / "value taken elsewhere" disclosure.
- **Docs**:
  https://filamentphp.com/docs/5.x/users/tenancy#unique-and-exists-validation

## E. Dependencies

### E1. Known vulnerabilities in Filament / Livewire / Filament plugins — `[Site]`

- **Search**: `composer audit --format=plain` from the project root. Cross-check
  package names against `composer.json` to identify which advisories affect
  Filament (`filament/*`), Livewire (`livewire/livewire`), or installed Filament
  plugins (any package whose name or description references Filament). Unrelated
  framework / library CVEs are out of scope for this audit.
- **Flag if**: `composer audit` reports an in-scope advisory with a fixed
  version available. One §2 entry per advisory.
- **Safe when**: no in-scope advisories, or the installed version already meets
  the advisory's fixed range.
- **Fix**: `composer update <package> --with-all-dependencies` and re-run
  `composer audit` to confirm the advisory is gone. If the fixed version is a
  major bump, link the package's upgrade guide in the §2 entry rather than
  pasting the command alone.
- **Why**: Filament and Livewire CVEs typically affect every panel using the
  vulnerable version — usage-level mitigations don't apply. Outdated-but-
  unaffected packages are not a finding (this is not a "stay current" check).
- **Docs**: https://getcomposer.org/doc/03-cli.md#audit
