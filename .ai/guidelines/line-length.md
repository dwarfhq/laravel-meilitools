# Line length

Keep lines at 120 characters or fewer. Treat 120 as the target and 140 as a hard limit: a line over
140 characters must be broken across several lines.

- The limit applies to code, PHPDoc and comments alike. A trailing comment that pushes a statement
  past the limit belongs on its own line above the statement.
- A literal string is exempt. Do not concatenate or wrap one to satisfy the limit — the content is
  the length, and splitting it makes the value harder to read and to grep for. This covers
  translation lines, long URLs, SQL and test fixtures.
- Break a line by giving its parts structure, never by making the code less clear. Put chained
  calls one per line, expand an arrow function into a multi-line closure or a named method, and
  spread array literals and long argument lists across lines.
- Never shorten a line by removing a type hint, abbreviating a name, or dropping a named argument.
  A long line is a formatting problem; those are worse problems.

Pint does not enforce this — PHP-CS-Fixer has no line-length rule — so it is on the author. Files
that predate the guideline are not required to be reformatted; apply it to lines you touch.
