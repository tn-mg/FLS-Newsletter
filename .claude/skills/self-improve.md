# Self-Improve Skill

Whenever the user corrects my approach, confirms a non-obvious decision, or finalizes a plan, I MUST automatically update the project's memory system so future conversations retain context.

## Trigger Conditions

Activate this skill when any of the following happen:

1. **User gives corrective feedback**: "no not that", "don't do X", "stop doing Y", "I prefer Z instead"
2. **User confirms an approach**: "yes exactly", "perfect, keep doing that", "that works"
3. **A decision is finalized**: The user agrees to a specific implementation plan, URL format, architecture choice, tool preference, etc.
4. **New project context is revealed**: Deadlines, stakeholder constraints, team structure, migration plans, hosting choices, etc.
5. **User explicitly asks to remember something**

## Memory Types

Choose the correct type based on what was learned:

### 1. `feedback` — How I should approach work

Save when the user corrects OR confirms a non-obvious approach.

**What to capture:**
- The rule/guidance itself
- **Why:** The reason given — often a past incident or strong preference
- **How to apply:** When/where this guidance kicks in

**File naming:** `feedback_<topic>.md` (e.g. `feedback_security-and-url.md`)

**Example triggers:**
- "don't mock the database — we got burned last quarter"
- "yeah the single bundled PR was the right call"
- "stop summarizing what you did at the end of every response"

### 2. `project` — Ongoing work, goals, constraints

Save when I learn who is doing what, why, or by when.

**What to capture:**
- The fact or decision
- **Why:** The motivation — constraint, deadline, stakeholder ask, compliance need
- **How to apply:** How this should shape my suggestions
- Convert relative dates to absolute dates (e.g. "Thursday" → "2026-03-05")

**File naming:** `project_<topic>.md` (e.g. `project_newsletter-migration.md`)

**Example triggers:**
- "we're freezing merges after Thursday — mobile team is cutting a release"
- "the auth middleware rewrite is driven by legal/compliance"
- "the newsletter migration deadline is end of Q2"

### 3. `user` — User's role, goals, knowledge

Save when I learn details about the user's perspective.

**What to capture:**
- Role, experience level, domain expertise
- Preferences about collaboration style
- Tools/workflows they are comfortable with

**File naming:** `user_<topic>.md` (e.g. `user_workflow.md`)

**Example triggers:**
- "I've been writing Go for ten years but this is my first React project"
- "I prefer custom code over off-the-shelf plugins"
- "I manage this site solo, no dev team"

### 4. `reference` — Pointers to external systems

Save when I learn where information lives outside the project.

**What to capture:**
- External system name and purpose
- How to access it
- When to check it

**File naming:** `reference_<topic>.md`

## Steps to Save Memory

1. **Check for duplicates** — Read `MEMORY.md` and existing memory files. If a similar memory exists, update it instead of creating a duplicate.

2. **Write/update the memory file** using this frontmatter format:

```markdown
---
name: {{concise name}}
description: {{one-line description — used to decide relevance in future conversations}}
type: {{user|feedback|project|reference}}
---

{{memory content}}
```

For `feedback` and `project` types, structure the body as:
- Rule/fact
- **Why:** (reason)
- **How to apply:** (when/where this kicks in)

3. **Update `MEMORY.md`** — Add a pointer line (NOT the full content):
```markdown
- [Title](file.md) — one-line hook
```

4. **Update `AGENTS.md`** if the decision changes project-level instructions (e.g. new hosting approach, URL format decision, architectural choice). `AGENTS.md` is the single source of truth for project rules — `CLAUDE.md` just points to it (`@AGENTS.md`), so never edit `CLAUDE.md` directly. Add the change to the relevant existing section, or create a new one.

## What NOT to Save

- Code patterns, conventions, file paths — derive from current code
- Git history, recent changes — use `git log`/`git blame`
- Debugging solutions or fix recipes — the fix is in the code
- Anything already documented in AGENTS.md (just reference it)
- Ephemeral task details, in-progress work, temporary state

## Auto-Save Checklist

After every exchange where the user gives feedback or a decision is made, ask myself:

- [ ] Did the user correct me? → Save `feedback`
- [ ] Did the user confirm an unusual approach? → Save `feedback`
- [ ] Did we finalize a project decision? → Save `project` + update `CLAUDE.md`
- [ ] Did I learn something about the user's role/preferences? → Save `user`
- [ ] Did I learn about an external system/resource? → Save `reference`
- [ ] Did I already save something similar? → Update existing instead of duplicate
