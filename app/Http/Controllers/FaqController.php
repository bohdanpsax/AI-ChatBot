<?php

namespace App\Http\Controllers;

use App\Models\Campsite;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FaqController extends Controller
{
    public function index(Campsite $campsite, Request $request): JsonResponse
    {
        $query = $campsite->faqs();

        if ($request->has('language')) {
            $query->where('language', $request->language);
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->boolean('active_only', true)) {
            $query->where('is_active', true);
        }

        $faqs = $query->orderBy('sort_order')->orderBy('category')->get();

        return response()->json(['data' => $faqs]);
    }

    public function store(Campsite $campsite, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'question'   => 'required|string|max:500',
            'answer'     => 'required|string',
            'category'   => 'required|in:general,rules,facilities,pricing,activities',
            'language'   => 'required|string|size:2',
            'is_active'  => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $faq = $campsite->faqs()->create($validator->validated());

        return response()->json(['data' => $faq], 201);
    }

    public function update(Campsite $campsite, Faq $faq, Request $request): JsonResponse
    {
        if ($faq->campsite_id !== $campsite->id) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'question'   => 'sometimes|string|max:500',
            'answer'     => 'sometimes|string',
            'category'   => 'sometimes|in:general,rules,facilities,pricing,activities',
            'language'   => 'sometimes|string|size:2',
            'is_active'  => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $faq->update($validator->validated());

        return response()->json(['data' => $faq]);
    }

    public function destroy(Campsite $campsite, Faq $faq): JsonResponse
    {
        if ($faq->campsite_id !== $campsite->id) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $faq->delete();

        return response()->json(['message' => 'FAQ deleted successfully']);
    }
}