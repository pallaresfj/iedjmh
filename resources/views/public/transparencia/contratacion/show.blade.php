@extends('layouts.public.app')

@section('title', $contract['process_code'])

@section('content')
    <x-public.internal-page :title="$contract['process_code']" :lead="$contract['object']" :banner="$banner" section-key="transparencia" :force-banner-title-style="true">
        <x-slot:sidebar>
            <x-public.transparencia.sidebar :categories="$categories" active-section="contratacion" />

            <div class="public-surface p-4 sm:p-5">
                <p class="public-heading text-sm font-semibold uppercase tracking-wide text-ied-gray-900">Volver</p>
                <a href="{{ route('transparencia.contratacion.index') }}" class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-ied-primary-dark hover:text-ied-primary">
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H6.06l3.72 3.72a.75.75 0 11-1.06 1.06l-5-5a.75.75 0 010-1.06l5-5a.75.75 0 011.06 1.06L6.06 9.25h10.19A.75.75 0 0117 10z" clip-rule="evenodd" />
                    </svg>
                    Listado de contratacion
                </a>
            </div>
        </x-slot:sidebar>

        <div class="space-y-6">
            <section class="public-surface p-5 sm:p-6">
                <h2 class="public-heading text-xl font-semibold text-ied-gray-900">Ficha del proceso</h2>

                @php($amountLabel = $contract['official_budget'] !== null ? '$'.number_format($contract['official_budget'], 0, ',', '.') : 'No definido')

                <dl class="mt-4 grid gap-3 text-sm text-ied-gray-700 sm:grid-cols-2">
                    <div>
                        <dt class="font-semibold text-ied-gray-900">Tipo de contrato</dt>
                        <dd>{{ $contract['type'] ?: 'No registrado' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-ied-gray-900">Vigencia</dt>
                        <dd>{{ $contract['fiscal_year'] }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-ied-gray-900">Estado del proceso</dt>
                        <dd>{{ $contract['process_status_label'] }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-ied-gray-900">Cuantia</dt>
                        <dd>{{ $amountLabel }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-ied-gray-900">Publicacion</dt>
                        <dd>{{ $contract['publication_date'] ?? 'No registrada' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-ied-gray-900">Cierre de ofertas</dt>
                        <dd>{{ $contract['offers_deadline_date'] ?? 'No registrado' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-ied-gray-900">Evaluacion</dt>
                        <dd>{{ $contract['evaluation_date'] ?? 'No registrada' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-ied-gray-900">Adjudicacion</dt>
                        <dd>{{ $contract['award_date'] ?? 'No registrada' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="public-surface p-5 sm:p-6">
                <h2 class="public-heading text-xl font-semibold text-ied-gray-900">Etapa de Convocatoria (Ofertas)</h2>
                @php($convocatoriaItems = $documentsByStage['convocatoria']['items'])

                @if ($convocatoriaItems->isEmpty())
                    <div class="mt-4 rounded-xl border border-dashed border-ied-gray-200 bg-ied-gray-100 p-4 text-sm text-ied-gray-700">
                        No hay documentos de convocatoria publicados para este proceso.
                    </div>
                @else
                    <div class="mt-4 space-y-3">
                        @foreach ($convocatoriaItems as $item)
                            <article class="rounded-xl border border-ied-gray-200 bg-white p-4 text-sm">
                                <p class="font-semibold text-ied-gray-900">{{ $item['title'] }}</p>
                                <p class="mt-1 text-xs text-ied-gray-600">{{ $item['type_label'] }}</p>
                                @if ($item['url'])
                                    <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex items-center rounded-full border border-ied-primary/25 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-ied-primary-dark transition hover:border-ied-primary hover:text-ied-primary">
                                        Descargar
                                    </a>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="public-surface p-5 sm:p-6">
                <h2 class="public-heading text-xl font-semibold text-ied-gray-900">Etapa de Adjudicacion</h2>

                <dl class="mt-4 grid gap-3 text-sm text-ied-gray-700 sm:grid-cols-2">
                    <div>
                        <dt class="font-semibold text-ied-gray-900">Contratista</dt>
                        <dd>{{ $contract['contractor_name'] ?: 'No registrado' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-ied-gray-900">NIT</dt>
                        <dd>{{ $contract['contractor_nit'] ?: 'No registrado' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="font-semibold text-ied-gray-900">Objeto social</dt>
                        <dd>{{ $contract['contractor_social_object'] ?: 'No registrado' }}</dd>
                    </div>
                </dl>

                @php($participants = $contract['participants'] ?? collect())
                @if ($participants->isNotEmpty())
                    <div class="mt-5 overflow-x-auto rounded-xl border border-ied-gray-200">
                        <table class="min-w-full divide-y divide-ied-gray-200 text-sm">
                            <thead class="bg-ied-gray-100 text-left text-xs font-semibold uppercase tracking-wide text-ied-gray-700">
                                <tr>
                                    <th class="px-4 py-3">Participante</th>
                                    <th class="px-4 py-3">NIT</th>
                                    <th class="px-4 py-3">Objeto social</th>
                                    <th class="px-4 py-3">Puntaje</th>
                                    <th class="px-4 py-3">Resultado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ied-gray-200 bg-white text-ied-gray-700">
                                @foreach ($participants as $participant)
                                    <tr>
                                        <td class="px-4 py-3 font-medium text-ied-gray-900">{{ $participant['name'] }}</td>
                                        <td class="px-4 py-3">{{ $participant['nit'] ?: 'No registrado' }}</td>
                                        <td class="px-4 py-3">{{ $participant['social_object'] ?: 'No registrado' }}</td>
                                        <td class="px-4 py-3">{{ $participant['evaluation_score'] !== null ? number_format($participant['evaluation_score'], 2, ',', '.') : 'No registrado' }}</td>
                                        <td class="px-4 py-3">
                                            @if ($participant['is_awarded'])
                                                <span class="inline-flex rounded-full bg-ied-primary/10 px-2.5 py-1 text-xs font-semibold text-ied-primary-dark">Adjudicado</span>
                                            @else
                                                <span class="text-xs text-ied-gray-600">No adjudicado</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @if ($contract['secop_ii_url'])
                    <a href="{{ $contract['secop_ii_url'] }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex items-center rounded-full bg-ied-primary px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-ied-primary-dark">
                        Ir a SECOP II
                    </a>
                @endif

                @php($adjudicacionItems = $documentsByStage['adjudicacion']['items'])

                @if ($adjudicacionItems->isEmpty())
                    <div class="mt-4 rounded-xl border border-dashed border-ied-gray-200 bg-ied-gray-100 p-4 text-sm text-ied-gray-700">
                        No hay documentos de adjudicacion publicados para este proceso.
                    </div>
                @else
                    <div class="mt-4 space-y-3">
                        @foreach ($adjudicacionItems as $item)
                            <article class="rounded-xl border border-ied-gray-200 bg-white p-4 text-sm">
                                <p class="font-semibold text-ied-gray-900">{{ $item['title'] }}</p>
                                <p class="mt-1 text-xs text-ied-gray-600">{{ $item['type_label'] }}</p>
                                @if ($item['url'])
                                    <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex items-center rounded-full border border-ied-primary/25 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-ied-primary-dark transition hover:border-ied-primary hover:text-ied-primary">
                                        Descargar
                                    </a>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            @php($supportItems = $documentsByStage['soporte']['items'])
            @if ($supportItems->isNotEmpty())
                <section class="public-surface p-5 sm:p-6">
                    <h2 class="public-heading text-xl font-semibold text-ied-gray-900">Documentos de soporte</h2>
                    <div class="mt-4 space-y-3">
                        @foreach ($supportItems as $item)
                            <article class="rounded-xl border border-ied-gray-200 bg-white p-4 text-sm">
                                <p class="font-semibold text-ied-gray-900">{{ $item['title'] }}</p>
                                <p class="mt-1 text-xs text-ied-gray-600">{{ $item['type_label'] }}</p>
                                @if ($item['url'])
                                    <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex items-center rounded-full border border-ied-primary/25 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-ied-primary-dark transition hover:border-ied-primary hover:text-ied-primary">
                                        Descargar
                                    </a>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </x-public.internal-page>
@endsection
