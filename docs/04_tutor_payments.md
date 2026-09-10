# Payments & Financial Logic (Tutor View)

## Core Concept: Calendar-Driven Billing
* Financial debt is strictly tied to the presence of an event in the [[03_tutor_calendar]]. There is no manual invoice generation.

## Student Financial Settings
Configured inside the [[02_tutor_students]] profile:
* `lesson_price`: The cost of a single lesson for this specific student.
* `billing_type`: Payment schedule ("After each lesson" or "End of the month").

## Automated Balance Tracking
* **Completed Lessons:** When the date and time of a scheduled calendar lesson pass, the system automatically marks it as "Completed" and adds the `lesson_price` to the student's balance.
* **Cancellations:** Deleting a lesson from the calendar automatically removes it from the financial calculation.