# Comments

Keep comments minimal unless asked for more. Code that follows default Laravel/PHP
conventions explains itself and needs none.

## PHPDoc

A single line stating what the member does is the default, and most members need
nothing else.

- Describe what the code does, never how it differs from a previous version or why it
  changed. Reasons for a change belong in the commit message, not in the file.
- Add a paragraph beyond the summary line only for behaviour a reader could not know
  without reading the code: a sign convention, a deliberate omission, a guard that
  looks redundant, an unusual response contract.
- Do not restate the signature in prose. Types, parameter names and return types
  already say it. `@param` array shapes and `@return` value contracts are part of the
  signature, not prose — keep those.

## Inline comments

An inline comment may only exist where the code deviates from default Laravel/PHP
conventions. If the line does what any Laravel developer would expect, it gets no
comment.

- Never narrate what the next line does, restate the method name, or justify that a
  change is correct.
- The same applies in tests: keep the arithmetic that makes a failing expectation
  diagnosable and drop the narration around it.
