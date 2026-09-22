<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>Lesson reminder</title>
</head>

<body
    style="
        margin: 0;
        padding: 32px;
        background: #f8fafc;
        font-family: Arial, sans-serif;
        color: #0f172a;
    "
>
    @php
        $reminderText = match ($reminderMinutes) {
            15 => '15 minutes',
            30 => '30 minutes',
            60 => '1 hour',
            120 => '2 hours',
            default => $reminderMinutes . ' minutes',
        };
    @endphp

    <div
        style="
            max-width: 560px;
            margin: 0 auto;
            padding: 28px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
        "
    >
        <div
            style="
                margin-bottom: 24px;
                font-size: 18px;
                font-weight: 700;
            "
        >
            Tutorly
        </div>

        <h1
            style="
                margin: 0;
                font-size: 22px;
            "
        >
            Your lesson starts soon
        </h1>

        <p
            style="
                margin-top: 20px;
                line-height: 1.6;
                color: #475569;
            "
        >
            Hi {{ $lesson->tutorStudent->student->name }},
        </p>

        <p
            style="
                line-height: 1.6;
                color: #475569;
            "
        >
            Your
            <strong>
                {{ $lesson->tutorStudent->subject }}
            </strong>
            lesson with
            <strong>
                {{ $lesson->tutorStudent->tutor->name }}
            </strong>
            starts in
            <strong>
                {{ $reminderText }}
            </strong>.
        </p>

        <p
            style="
                margin-top: 28px;
                font-size: 13px;
                color: #94a3b8;
            "
        >
            This is an automatic reminder from Tutorly.
        </p>
    </div>
</body>
</html>