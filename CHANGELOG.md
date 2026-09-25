# Changelog

Release notes for SeAT Capitals. Each version lists what an operator sees after upgrading.

## 1.0.0 - 2026-09-25

- Capital build applications: members with `capitals.apply` submit, follow and withdraw applications
  for their own characters.
- Review screen: users with `capitals.review` approve or deny pending applications with an optional
  note. Decisions are written to SeAT's security log.
- Capital report: users with `character.capitals` see every capital hull owned by the characters in
  their permission scope, with the owner's main, current corporation, and the system each hull sits
  in. Optional system filter, and a character scope selector that defaults to including same-account
  alts; `capitals.report_all` unlocks an "Everyone in SeAT" scope that ignores the role filters.
- Settings screen: home systems that pre-fill the report filter.
- Two notification alerts for SeAT notification groups: new application, and application decided.
- Publishable config listing which SDE groups count as capital hulls.
