# Roles And Access Overview

This is a concise intent-level guide. The actual enforcement lives in middleware, policies, and seeded permissions.

## Guest

- Not a database role
- Public-only state
- Cannot access authenticated features, protected downloads, or internal/admin routes

## User

- Standard authenticated customer/user account
- Can manage own profile, sessions, and devices
- Can create client requests and access protected downloads where allowed
- Cannot enter internal/admin flows

## SuperAdmin

- Full privileged override through the authorization layer
- Only role that should handle the highest-risk internal actions such as privileged account provisioning and restricted CMS governance
- Approval and publishing boundaries still matter operationally even with override capability

## Admin

- Internal admin access with broad operational and CMS visibility
- Can manage many user/content flows
- Does not replace SuperAdmin for top-tier privileged actions

## Editor

- Focused on CMS content work
- Can draft, compose, preview, and submit for review
- Does not represent final publish authority

## Product Manager

- Internal coordination role for product/content shaping and request visibility
- Can work with CMS drafts and previews
- Still operates inside the approval flow

## Sales Manager

- Internal role for request-facing and communication-oriented work
- Can see request flows and approval queue visibility where seeded
- Not a publishing override role

## Developer

- Internal technical role
- Useful for advanced CMS/page work and platform QA
- Does not inherently bypass approval or SuperAdmin boundaries

## Support / Operations

- Internal operational support role
- Can inspect certain request/security contexts and preview content
- Should remain easy to operate for non-developers and not depend on hidden shortcuts

## Publishing And Approval Boundaries

- Drafting and preview are not the same as approval
- Approval is not the same as publish
- SuperAdmin-restricted actions stay restricted even if another role can see surrounding screens
- Public signup creates standard `user` accounts only
- Internal privileged accounts are provisioned explicitly rather than through public registration
