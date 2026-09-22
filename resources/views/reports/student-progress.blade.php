<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>
        Student progress report
    </title>

    <style>
        @page {
            margin: 32px;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #0f172a;
        }

        h1,
        h2,
        p {
            margin-top: 0;
        }

        .header {
            margin-bottom: 28px;
            padding-bottom: 18px;
            border-bottom: 1px solid #cbd5e1;
        }

        .brand {
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: bold;
            color: #475569;
        }

        .title {
            margin-bottom: 6px;
            font-size: 24px;
            font-weight: bold;
        }

        .subtitle {
            margin-bottom: 0;
            color: #64748b;
        }

        .section {
            margin-top: 24px;
        }

        .section-title {
            margin-bottom: 10px;
            font-size: 15px;
            font-weight: bold;
        }

        .info-table,
        .stats-table,
        .assignments-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 6px 0;
            vertical-align: top;
        }

        .info-label {
            width: 150px;
            color: #64748b;
        }

        .stats-table td {
            width: 33.33%;
            padding: 12px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .stat-label {
            margin-bottom: 4px;
            font-size: 10px;
            text-transform: uppercase;
            color: #64748b;
        }

        .stat-value {
            font-size: 18px;
            font-weight: bold;
        }

        .assignments-table th,
        .assignments-table td {
            padding: 8px;
            border: 1px solid #e2e8f0;
            text-align: left;
            vertical-align: top;
        }

        .assignments-table th {
            background: #f8fafc;
            font-size: 10px;
            color: #475569;
        }

        .muted {
            color: #64748b;
        }

        .footer {
            margin-top: 32px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
            font-size: 10px;
            color: #94a3b8;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="brand">
            Tutorly
        </div>

        <div class="title">
            Student Progress Report
        </div>

        <p class="subtitle">
            Generated {{ $generatedAt->format('d M Y, H:i') }} UTC
        </p>
    </div>

    <div class="section">
        <div class="section-title">
            Student information
        </div>

        <table class="info-table">
            <tr>
                <td class="info-label">
                    Student
                </td>

                <td>
                    <strong>{{ $student->name }}</strong>
                </td>
            </tr>

            <tr>
                <td class="info-label">
                    Email
                </td>

                <td>
                    {{ $student->email }}
                </td>
            </tr>

            <tr>
                <td class="info-label">
                    Tutor
                </td>

                <td>
                    {{ $tutor->name }}
                </td>
            </tr>

            <tr>
                <td class="info-label">
                    Subjects
                </td>

                <td>
                    {{
                        $subjects->isNotEmpty()
                            ? $subjects->join(', ')
                            : '—'
                    }}
                </td>
            </tr>

            <tr>
                <td class="info-label">
                    Connected since
                </td>

                <td>
                    {{
                        $linkedAt
                            ? $linkedAt->format('d M Y')
                            : '—'
                    }}
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">
            Lessons
        </div>

        <table class="stats-table">
            <tr>
                <td>
                    <div class="stat-label">
                        Total lessons
                    </div>

                    <div class="stat-value">
                        {{ $lessonStats['total'] }}
                    </div>
                </td>

                <td>
                    <div class="stat-label">
                        Completed
                    </div>

                    <div class="stat-value">
                        {{ $lessonStats['completed'] }}
                    </div>
                </td>

                <td>
                    <div class="stat-label">
                        Scheduled
                    </div>

                    <div class="stat-value">
                        {{ $lessonStats['scheduled'] }}
                    </div>
                </td>
            </tr>

            <tr>
                <td>
                    <div class="stat-label">
                        Cancelled
                    </div>

                    <div class="stat-value">
                        {{ $lessonStats['cancelled'] }}
                    </div>
                </td>

                <td>
                    <div class="stat-label">
                        Assignments
                    </div>

                    <div class="stat-value">
                        {{ $assignmentStats['total'] }}
                    </div>
                </td>

                <td>
                    <div class="stat-label">
                        Average grade
                    </div>

                    <div class="stat-value">
                        {{
                            $assignmentStats['averageGrade'] !== null
                                ? $assignmentStats['averageGrade'] . '%'
                                : '—'
                        }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">
            Recent assignments
        </div>

        @if ($recentAssignments->isEmpty())
            <p class="muted">
                No assignments yet.
            </p>
        @else
            <table class="assignments-table">
                <thead>
                    <tr>
                        <th>
                            Assignment
                        </th>

                        <th>
                            Subject
                        </th>

                        <th>
                            Deadline
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Grade
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($recentAssignments as $assignment)
                        <tr>
                            <td>
                                {{ $assignment['title'] }}
                            </td>

                            <td>
                                {{ $assignment['subject'] ?? '—' }}
                            </td>

                            <td>
                                {{
                                    $assignment['deadline']
                                        ? $assignment['deadline']->format('d M Y')
                                        : '—'
                                }}
                            </td>

                            <td>
                                {{
                                    match ($assignment['status']) {
                                        'awaiting_review' => 'Awaiting review',
                                        'graded' => 'Graded',
                                        default => 'To do',
                                    }
                                }}
                            </td>

                            <td>
                                {{
                                    $assignment['grade'] !== null
                                        ? $assignment['grade'] . '%'
                                        : '—'
                                }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="footer">
        Tutorly · Student Progress Report
    </div>
</body>
</html>