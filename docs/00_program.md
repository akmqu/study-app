# Tutoring Platform - System Architecture & Overview

## General Concept
A multi-tutor SaaS platform where independent tutors manage their students, schedules, assignments, and payments, and students have a single unified account to track multiple courses from different teachers.

## Technology Stack & Infrastructure
* **Backend:** Laravel (PHP)
* **Frontend:** Vue.js with Inertia.js and Tailwind CSS
* **Database & Migrations:** Relational database managed via Laravel Migrations
* **Background Tasks & Caching:** Redis, Queue worker, and Cron jobs (for automated lesson reminders)
* **Email Testing:** Mailpit (local development environment)
* **AI Integration:** OpenAI API (ChatGPT)/Gemini 1.5 Flash for automated homework generation(just think about it)
* **Payments:** Stripe or TPay integration for billing and subscriptions
* **Document & Data Management:** PDF generation utilities, CSV/Excel/JSON reporting and data import tools
* **Developer Tools:** Laravel Debugbar, Laravel Telescope, and Laravel IDE Helper

## Mandatory Requirements & Best Practices
* **Version Control & Documentation:** Git for code versioning; Obsidian for project documentation.
* **Architecture Standards:** Clean modular code structure following proper backend and frontend separation via Inertia.
* **Core Reliability:** Automated checks for cron tasks, queue processing for heavy reports (PDF generation), and secure multi-tutor data isolation.

## Core Modules & Navigation

### Tutor Space
* [[01_tutor_dashboard]] - Home screen, quick stats, and quick-action homework assignment with AI.
* [[02_tutor_students]] - Student management, profile cards, private notes, direct homework assignment, submissions review (including PDFs), and invitation flow.
* [[03_tutor_calendar]] - Lesson scheduling and automated email reminders (CRON).
* [[04_tutor_payments]] - Calendar-driven financial tracking and automated balances.

### Student Space
* [[05_student_dashboard]] - Student home view (upcoming tasks, active subjects, quick notes).
* [[06_student_assignments]] - Submitting homework, viewing grades, and resubmitting files.
* [[07_student_payments]] - Subscription status, payment history, and billing plans.
* [[08_student_courses]] - Overview of active courses, teachers, and prices.