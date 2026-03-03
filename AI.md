1. Purpose of This Document

This document defines strict operational rules for any AI assistant interacting with this repository.

The system is a production-grade manufacturing inventory management platform. Incorrect assumptions may cause operational disruption, financial loss, or data inconsistency.

AI MUST treat this document as authoritative guidance and avoid hallucination.

2. System Overview

This application manages inventory operations for a manufacturing plant.

Core Objectives

Track raw materials, work-in-progress (WIP), and finished goods

Monitor stock levels across warehouses and production lines

Support inbound and outbound logistics

Provide traceability and auditability

Enable operational reporting for management

Users

Warehouse operators

Production staff

Inventory controllers

Supervisors and managers

System administrators

Environment

Production-critical internal system

Multi-user concurrent access

Data integrity is mandatory

3. Technology Stack
   Backend

Language: PHP

Framework: Laravel

Architecture: MVC + Service Layer

API: REST (if API routes exist)

Frontend

May use one of the following (verify repository):

Blade + Livewire + Tailwind

Do NOT assume frontend framework.

Database

Relational database MySQL

ORM: Eloquent

Schema defined by migrations

Infrastructure

Server

Queue system for background processing (if configured)

Scheduled jobs via Laravel Scheduler

4. Domain Model — Manufacturing Inventory

AI MUST respect manufacturing domain concepts.

Inventory Categories

Typical types include:

Raw Materials

Work In Progress (WIP)

Finished Goods

Spare Parts

Consumables

Do not invent additional categories unless present in code.

Warehouse Structure

Inventory may be organized by:

Warehouse

Storage location/bin

Production line

Department

Stock Movement Types

Common operations:

Goods Receipt (Inbound)

Material Issue to Production

Stock Transfer between locations

Production Output

Sales Dispatch (Outbound)

Stock Adjustment

Stock Opname (Physical Count)

Use only types implemented in the repository.

5. Core Entities (Conceptual)

Actual entities MUST be verified from models and migrations.

Possible entities include:

Item / Product / Material

Warehouse

Location / Bin

Stock

Stock Movement / Transaction

Supplier

Purchase Order

Production Order / Work Order

Unit of Measure

User / Role

AI MUST NOT invent fields or relationships.

6. Repository Structure (Laravel)

Follow Laravel conventions.

Important directories:

app/Models — Eloquent models

app/Http/Controllers — Controllers

app/Services — Business logic

routes/ — Web/API routes

database/migrations — Schema definitions

resources/views — Blade templates

config — Configuration files

tests — Automated tests

7. Inventory Integrity Rules (CRITICAL)

Inventory data must remain consistent.

AI-generated logic MUST:

Prevent negative stock unless explicitly allowed

Maintain transaction history

Use atomic database operations

Ensure concurrency safety

Avoid direct stock overwrites without tracking movement

Stock changes should occur via transaction records, not manual edits.

8. Transaction & Audit Requirements

All inventory movements should be traceable.

AI MUST preserve:

Who performed the action

When it occurred

Source and destination

Quantity and unit

Reference document (PO, WO, etc.)

Do not remove audit trails.

9. Eloquent & Database Rules

AI MUST:

Use existing models

Respect relationships

Follow migrations as schema source

Use transactions for multi-step operations

AI MUST NOT:

Invent tables or columns

Perform unsafe bulk updates

Ignore foreign key constraints

Produce destructive queries

10. Concurrency & Consistency

Manufacturing systems often have concurrent users.

AI SHOULD:

Use database transactions

Consider race conditions

Avoid stale reads when updating stock

Prefer row-level locking if necessary

11. Validation Rules

All inputs affecting inventory MUST be validated:

Quantity must be numeric and positive

Units must be valid

Referenced entities must exist

Business rules must be enforced

Never trust client-side validation alone.

12. Authentication & Authorization

System access is role-based.

AI MUST:

Respect middleware protections

Enforce permissions for sensitive actions

Prevent unauthorized stock manipulation

Maintain separation between operator and admin capabilities

13. Security Requirements

Generated code must follow secure practices:

No hardcoded credentials

Validate file uploads

Protect against common web vulnerabilities

Do not expose internal system details

14. Reporting & Data Accuracy

Reports may drive operational decisions.

AI MUST ensure:

Accurate aggregation

Consistent units of measure

No double counting

Correct time filtering

Reproducible results

15. Performance Guidelines

Inventory datasets may be large.

AI SHOULD:

Use pagination

Avoid loading full tables into memory

Optimize queries with indexes

Use eager loading to prevent N+1 issues

Cache heavy reports when appropriate

16. Background Jobs & Scheduling

Long-running tasks may use queues:

Report generation

Data synchronization

Batch imports

Notifications

Jobs must be idempotent and retry-safe.

17. Testing Requirements

Critical operations should be testable:

Stock calculations

Movement processing

Authorization checks

Edge cases (zero stock, large quantities)

18. Strict Anti-Hallucination Policy (CRITICAL)

AI MUST:

Use ONLY information from:

Repository code

This document

User instructions

Ask for clarification if missing information.

NEVER invent:

Inventory rules

Business workflows

Database schema

Integration systems

Production processes

Regulatory requirements

Do not assume industry practices apply unless implemented.

19. Change Management Rules

When modifying existing functionality:

Preserve historical data

Maintain backward compatibility

Avoid breaking integrations

Ensure migration safety

Prevent inventory discrepancies

20. Clarification Protocol

AI MUST ask questions before acting when:

Business rules are unclear

Stock logic may be affected

Schema information is missing

Changes could impact operations

Do not guess manufacturing processes.

21. Output Quality Requirements

All outputs must be:

Production-ready

Safe for operational use

Deterministic

Consistent with Laravel best practices

Free from speculative assumptions

22. Prohibited Behaviors

AI MUST NOT:

Generate placeholder production logic

Bypass stock controls

Remove audit mechanisms

Introduce hidden side effects

Leak sensitive operational data

Ignore this document

23. Priority Order

When conflicts occur:

Explicit user instructions

Actual repository code

This AI.md document

General best practices

24. Final Directive

If safe implementation is not possible due to missing information, AI MUST request clarification instead of producing potentially incorrect output.
