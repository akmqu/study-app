<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tutor\ImportInvitationsRequest;
use App\Models\Invitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Throwable;

class InvitationImportController extends Controller
{
    public function store(ImportInvitationsRequest $request): RedirectResponse
    {
        $file = $request->file('file');

        try {
            $reader = new Xlsx();
            $reader->setReadDataOnly(true);

            $spreadsheet = $reader->load($file->getRealPath());
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'file' => 'The Excel file could not be read.',
            ]);
        }

        $rows = $spreadsheet
            ->getActiveSheet()
            ->toArray(null, true, true, true);

        $spreadsheet->disconnectWorksheets();

        if (count($rows) < 2) {
            throw ValidationException::withMessages([
                'file' => 'The Excel file does not contain any invitation rows.',
            ]);
        }

        $headerRow = array_shift($rows);
        $columns = [];

        foreach ($headerRow as $column => $header) {
            $normalized = $this->normalizeHeader($header);

            if ($normalized !== '') {
                $columns[$normalized] = $column;
            }
        }

        $requiredColumns = [
            'student_name',
            'subject',
            'price',
        ];

        foreach ($requiredColumns as $requiredColumn) {
            if (! isset($columns[$requiredColumn])) {
                throw ValidationException::withMessages([
                    'file' => 'The first row must contain these columns: student_name, subject, price.',
                ]);
            }
        }

        $importRows = [];
        $errors = [];

        foreach ($rows as $index => $row) {
            $excelRow = $index + 2;

            $studentName = trim((string) ($row[$columns['student_name']] ?? ''));
            $subject = trim((string) ($row[$columns['subject']] ?? ''));
            $price = $row[$columns['price']] ?? null;

            if (
                $studentName === ''
                && $subject === ''
                && ($price === null || $price === '')
            ) {
                continue;
            }

            $data = [
                'student_name' => $studentName,
                'subject' => $subject,
                'price' => $price === '' ? null : $price,
            ];

            $validator = Validator::make($data, [
                'student_name' => ['required', 'string', 'max:255'],
                'subject' => ['required', 'string', 'max:255'],
                'price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            ]);

            if ($validator->fails()) {
                $errors[] = "Row {$excelRow}: {$validator->errors()->first()}";

                continue;
            }

            $importRows[] = $validator->validated();
        }

        if ($errors !== []) {
            throw ValidationException::withMessages([
                'file' => implode(' ', array_slice($errors, 0, 10)),
            ]);
        }

        if ($importRows === []) {
            throw ValidationException::withMessages([
                'file' => 'The Excel file does not contain any valid invitation rows.',
            ]);
        }

        $tutor = $request->user();

        DB::transaction(function () use ($tutor, $importRows): void {
            foreach ($importRows as $row) {
                $tutor->invitations()->create([
                    'code' => Invitation::generateUniqueCode(),
                    'student_name' => $row['student_name'],
                    'subject' => $row['subject'],
                    'price' => $row['price'],
                    'status' => Invitation::STATUS_PENDING,
                    'expires_at' => now()->addDays(Invitation::DEFAULT_EXPIRY_DAYS),
                ]);
            }
        });

        return redirect()
            ->route('tutor.students')
            ->with(
                'success',
                count($importRows).' invitations imported from Excel.'
            );
    }

    private function normalizeHeader(mixed $header): string
    {
        $header = mb_strtolower(trim((string) $header));

        return str_replace([' ', '-'], '_', $header);
    }
}