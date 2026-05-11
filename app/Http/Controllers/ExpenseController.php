<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseController extends Controller
{
    public function exportCsv(): StreamedResponse
    {
        $expenses = auth()->user()->expenses()
            ->latest('expense_date')
            ->get();

        return response()->streamDownload(function () use ($expenses) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Date', 'Amount', 'Category', 'Description']);

            foreach ($expenses as $expense) {
                fputcsv($handle, [
                    $expense->expense_date->format('Y-m-d'),
                    number_format($expense->amount / 100, 2),
                    $expense->category,
                    $expense->description ?? '',
                ]);
            }

            fclose($handle);
        }, 'my_expenses.csv', ['Content-Type' => 'text/csv']);
    }

    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->expenses()->latest('expense_date');

        if ($request->boolean('current_month')) {
            $query->whereYear('expense_date', now()->year)
                  ->whereMonth('expense_date', now()->month);
        }

        $expenses = $query->get();

        return response()->json([
            'data' => $expenses,
            'meta' => [
                'count'        => $expenses->count(),
                'total_amount' => $expenses->sum('amount'),
            ],
        ]);
    }

    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $expense = $request->user()->expenses()->create($request->validated());

        return response()->json($expense, Response::HTTP_CREATED);
    }

    public function update(UpdateExpenseRequest $request, int $id): JsonResponse
    {
        $expense = $request->user()->expenses()->findOrFail($id);

        $expense->update($request->validated());

        return response()->json($expense);
    }

    public function destroy(Request $request, int $id): Response
    {
        $request->user()->expenses()->findOrFail($id)->delete();

        return response()->noContent();
    }
}
