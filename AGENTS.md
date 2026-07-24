# Project Instructions

## UI and Layout Changes

Purely presentational UI changes must not result in new or modified automated
tests unless the user explicitly requests tests. This exception overrides any
general TDD instruction for changes limited to copy, styling, colors, spacing,
typography, responsive layout, or visual arrangement.

Continue to validate UI changes in the browser and run relevant formatting,
linting, and build checks. If a UI task changes application behavior—such as
forms, navigation, permissions, state, data handling, or interactions—use the
normal testing guidance for that behavioral change.
