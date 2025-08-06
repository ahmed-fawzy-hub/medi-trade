<?php

namespace App\Swagger;

/**
 * @OA\Schema(
 *     schema="Category",
 *     type="object",
 *     title="Category",
 *     required={"id", "name_en", "name_ar"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name_en", type="string", example="Drinks"),
 *     @OA\Property(property="name_ar", type="string", example="مشروبات"),
 *     @OA\Property(property="is_active", type="boolean", example=true)
 * )
 */
class Schema {}
