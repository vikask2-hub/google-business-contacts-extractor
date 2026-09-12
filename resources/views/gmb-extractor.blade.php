<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Discover business contact details by city and category for focused sales outreach.">
    <title>LocalReach · Business contacts explorer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@0.468.0/dist/umd/lucide.min.js"></script>
    <style>body, h1, h2, h3, select, button { font-family: Inter, ui-sans-serif, system-ui, sans-serif !important; }</style>
</head>
<body class="min-h-screen bg-[#f5f7fb] pt-16 text-slate-900 selection:bg-blue-600 selection:text-white">
    <div class="pointer-events-none fixed inset-0 opacity-45" style="background-image:radial-gradient(#cdd6e5 0.65px,transparent 0.65px);background-size:24px 24px;mask-image:linear-gradient(to bottom,black,transparent 65%)"></div>

    <header class="fixed inset-x-0 top-0 z-50 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl">
        <div class="mx-auto flex h-16 max-w-[1440px] items-center justify-between px-4 sm:px-6 lg:px-10">
            <x-portfolio-back-button />
            <div class="flex items-center gap-2.5">
                <span class="hidden items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-[9px] font-bold tracking-[.1em] text-slate-500 uppercase sm:inline-flex"><span class="size-1.5 rounded-full {{ $mode === 'live' ? 'animate-pulse bg-emerald-500' : 'bg-amber-400' }}"></span>{{ $mode === 'live' ? 'Google Places live' : 'Demo preview' }}</span>
                <span class="hidden text-right sm:block"><strong class="block text-[11px] font-extrabold text-slate-800">LocalReach</strong><small class="block text-[8px] font-bold tracking-[.1em] text-slate-400 uppercase">Contacts extractor</small></span>
            </div>
        </div>
    </header>

    <main class="relative z-10 mx-auto max-w-[1440px] px-4 py-5 sm:px-6 sm:py-7 lg:px-10">
        <section class="relative overflow-hidden rounded-[1.75rem] bg-[#081226] px-5 py-6 text-white shadow-[0_24px_70px_rgba(15,23,42,.18)] sm:px-8 sm:py-8 lg:px-10">
            <div class="pointer-events-none absolute inset-0" style="background:radial-gradient(circle at 88% 10%,rgba(37,99,235,.5),transparent 35%),radial-gradient(circle at 12% 120%,rgba(20,184,166,.25),transparent 38%)"></div>
            <div class="pointer-events-none absolute inset-y-0 right-0 w-1/2 opacity-[.09]" style="background-image:linear-gradient(rgba(255,255,255,.2) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.2) 1px,transparent 1px);background-size:32px 32px"></div>

            <div class="relative grid items-end gap-7 lg:grid-cols-[.9fr_1.1fr] lg:gap-12">
                <div>
                    <p class="text-[9px] font-bold tracking-[.18em] text-blue-300 uppercase">Local business intelligence</p>
                    <h1 class="mt-3 max-w-[650px] text-3xl font-extrabold tracking-[-.055em] text-white sm:text-4xl lg:text-[2.7rem] lg:leading-[1.02]">Find the right businesses.<br><span class="text-blue-300">Start better conversations.</span></h1>
                    <p class="mt-4 max-w-[580px] text-sm leading-6 text-slate-300">Choose a city and business category to build a clean, outreach-ready contact list in seconds.</p>
                    <div class="mt-5 flex flex-wrap gap-x-5 gap-y-2 text-[10px] font-semibold text-slate-300">
                        <span class="flex items-center gap-1.5"><i data-lucide="building-2" class="size-3.5 text-blue-300"></i> Business profiles</span>
                        <span class="flex items-center gap-1.5"><i data-lucide="phone" class="size-3.5 text-blue-300"></i> Public contacts</span>
                        <span class="flex items-center gap-1.5"><i data-lucide="download" class="size-3.5 text-blue-300"></i> CSV export</span>
                    </div>
                </div>

                <form method="GET" action="{{ route('gmb-extractor.search') }}" class="rounded-2xl border border-white/10 bg-white/[.08] p-4 backdrop-blur-md sm:p-5">
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <div><p class="text-[9px] font-bold tracking-[.14em] text-blue-300 uppercase">Search directory</p><h2 class="mt-1 text-base font-extrabold text-white">Choose your market</h2></div>
                        <span class="grid size-9 place-items-center rounded-xl bg-white/10 text-blue-200"><i data-lucide="sliders-horizontal" class="size-4"></i></span>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label for="city" class="text-[10px] font-bold tracking-wide text-slate-300">City</label>
                            <div class="relative"><i data-lucide="map-pin" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"></i><select id="city" name="city" class="h-12 border-white/10 bg-white pl-10 text-sm font-semibold text-slate-900" required>@foreach($cities as $value => $city)<option value="{{ $value }}" @selected($selectedCity === $value)>{{ $city['label'] }}</option>@endforeach</select></div>
                            @error('city')<p class="mt-1.5 text-xs font-semibold text-rose-300">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="category" class="text-[10px] font-bold tracking-wide text-slate-300">Business category</label>
                            <div class="relative"><i data-lucide="briefcase-business" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"></i><select id="category" name="category" class="h-12 border-white/10 bg-white pl-10 text-sm font-semibold text-slate-900" required>@foreach($categories as $value => $category)<option value="{{ $value }}" @selected($selectedCategory === $value)>{{ $category['label'] }}</option>@endforeach</select></div>
                            @error('category')<p class="mt-1.5 text-xs font-semibold text-rose-300">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <button class="mt-4 inline-flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-blue-500 px-5 text-sm font-extrabold text-white shadow-lg shadow-blue-950/30 transition hover:bg-blue-400 focus:ring-3 focus:ring-blue-300/30 focus:outline-none"><i data-lucide="search" class="size-4"></i> Find businesses</button>
                </form>
            </div>
        </section>

        <section class="mt-5 overflow-hidden rounded-[1.4rem] border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,.055)]">
            <div class="flex flex-col gap-4 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div class="flex items-center gap-3">
                    <span class="grid size-10 place-items-center rounded-xl bg-blue-50 text-blue-700"><i data-lucide="list-filter" class="size-[18px]"></i></span>
                    <div><div class="flex flex-wrap items-center gap-2"><h2 class="text-base font-extrabold tracking-[-.03em]">{{ count($results) }} businesses found</h2><span class="rounded-full {{ $mode === 'live' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }} px-2.5 py-1 text-[8px] font-extrabold tracking-[.1em] uppercase">{{ $mode === 'live' ? 'Live data' : 'Sample data' }}</span></div><p class="mt-0.5 text-[11px] text-slate-500">{{ $categories[$selectedCategory]['label'] }} in {{ $cities[$selectedCity]['label'] }}</p></div>
                </div>
                <button type="button" id="export-results" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"><i data-lucide="download" class="size-3.5"></i> Export CSV</button>
            </div>

            @if($notice)
                <div class="flex items-start gap-2.5 border-b border-amber-100 bg-amber-50/70 px-5 py-3 text-[11px] leading-5 text-amber-800 sm:px-6"><i data-lucide="info" class="mt-0.5 size-3.5 shrink-0"></i><p>{{ $notice }} Email addresses are discovered only when publicly displayed on the listed website.</p></div>
            @endif

            <div class="hidden overflow-x-auto md:block">
                <table>
                    <thead><tr><th class="w-[23%]">Business</th><th class="w-[22%]">City & address</th><th>Website</th><th>Email</th><th>Phone number</th><th class="text-right">Maps</th></tr></thead>
                    <tbody>
                        @forelse($results as $business)
                            <tr class="transition hover:bg-blue-50/35">
                                <td><div class="flex items-center gap-3"><span class="grid size-9 shrink-0 place-items-center rounded-xl bg-slate-100 text-[11px] font-extrabold text-slate-700">{{ collect(explode(' ', $business['name']))->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->join('') }}</span><strong class="block text-xs font-extrabold leading-5 text-slate-900">{{ $business['name'] }}</strong></div></td>
                                <td><strong class="block text-[11px] font-bold text-slate-700">{{ $business['city'] }}</strong><span class="mt-1 block max-w-[260px] text-[10px] leading-4 text-slate-400">{{ $business['address'] }}</span></td>
                                <td>@if($business['website'])<a href="{{ $business['website'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex max-w-[170px] items-center gap-1.5 truncate text-[11px] font-bold text-blue-700 hover:underline"><i data-lucide="globe-2" class="size-3.5 shrink-0"></i>{{ parse_url($business['website'], PHP_URL_HOST) }}</a>@else<span class="text-[10px] text-slate-400">Not listed</span>@endif</td>
                                <td>@if($business['email'])<a href="mailto:{{ $business['email'] }}" class="inline-flex max-w-[190px] items-center gap-1.5 truncate text-[11px] font-bold text-slate-700 hover:text-blue-700"><i data-lucide="mail" class="size-3.5 shrink-0 text-slate-400"></i>{{ $business['email'] }}</a>@else<span class="text-[10px] text-slate-400">Not publicly listed</span>@endif</td>
                                <td>@if($business['phone'])<a href="tel:{{ $business['phone'] }}" class="inline-flex items-center gap-1.5 whitespace-nowrap text-[11px] font-bold text-slate-700 hover:text-blue-700"><i data-lucide="phone" class="size-3.5 text-slate-400"></i>{{ $business['phone'] }}</a>@else<span class="text-[10px] text-slate-400">Not listed</span>@endif</td>
                                <td class="text-right">@if($business['maps_url'])<a href="{{ $business['maps_url'] }}" target="_blank" rel="noopener noreferrer" class="inline-grid size-8 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" aria-label="View {{ $business['name'] }} on Google Maps"><i data-lucide="arrow-up-right" class="size-3.5"></i></a>@endif</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-14 text-center"><i data-lucide="search-x" class="mx-auto size-7 text-slate-300"></i><p class="mt-3 text-sm font-bold text-slate-700">No businesses found</p><p class="mt-1 text-xs text-slate-400">Try another city or category.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="grid gap-3 p-3 md:hidden">
                @forelse($results as $business)
                    <article class="rounded-2xl border border-slate-200 p-4">
                        <div class="flex items-start gap-3"><span class="grid size-10 shrink-0 place-items-center rounded-xl bg-blue-50 text-xs font-extrabold text-blue-700">{{ collect(explode(' ', $business['name']))->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->join('') }}</span><div class="min-w-0"><h3 class="text-sm font-extrabold text-slate-900">{{ $business['name'] }}</h3><p class="mt-1 text-[10px] leading-4 text-slate-400">{{ $business['address'] }}</p></div></div>
                        <div class="mt-4 grid gap-2 text-[11px]"><p class="flex items-center gap-2 text-slate-600"><i data-lucide="phone" class="size-3.5 text-slate-400"></i>{{ $business['phone'] ?: 'Phone not listed' }}</p><p class="flex min-w-0 items-center gap-2 text-slate-600"><i data-lucide="mail" class="size-3.5 shrink-0 text-slate-400"></i><span class="truncate">{{ $business['email'] ?: 'Email not publicly listed' }}</span></p></div>
                        <div class="mt-4 flex gap-2">@if($business['website'])<a href="{{ $business['website'] }}" target="_blank" rel="noopener noreferrer" class="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-slate-100 px-3 py-2.5 text-[10px] font-bold text-slate-700"><i data-lucide="globe-2" class="size-3.5"></i> Website</a>@endif @if($business['maps_url'])<a href="{{ $business['maps_url'] }}" target="_blank" rel="noopener noreferrer" class="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-blue-600 px-3 py-2.5 text-[10px] font-bold text-white"><i data-lucide="map" class="size-3.5"></i> Google Maps</a>@endif</div>
                    </article>
                @empty
                    <div class="py-12 text-center text-sm text-slate-500">No matching businesses found.</div>
                @endforelse
            </div>
        </section>

        <footer class="flex flex-col gap-2 px-1 py-5 text-[9px] font-medium text-slate-400 sm:flex-row sm:items-center sm:justify-between"><p>Public business information for responsible, relevant outreach.</p><p>Google Places API ready · {{ count($cities) }} cities · {{ count($categories) }} categories</p></footer>
    </main>

    <script>
        const businesses = {{ Js::from($results) }};
        document.addEventListener('DOMContentLoaded', () => {
            window.lucide?.createIcons();
            document.getElementById('export-results')?.addEventListener('click', () => {
                const cells = [['Business name', 'City', 'Address', 'Website', 'Email', 'Phone number', 'Google Maps'], ...businesses.map((business) => [business.name, business.city, business.address, business.website ?? '', business.email ?? '', business.phone ?? '', business.maps_url ?? ''])];
                const csvCell = (value) => {
                    const text = String(value);
                    const safeText = /^[=+\-@\t\r]/.test(text.trimStart()) ? `'${text}` : text;

                    return `"${safeText.replaceAll('"', '""')}"`;
                };
                const csv = cells.map((row) => row.map(csvCell).join(',')).join('\n');
                const link = document.createElement('a');
                link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }));
                link.download = `business-contacts-${new Date().toISOString().slice(0, 10)}.csv`;
                link.click();
                URL.revokeObjectURL(link.href);
            });
        });
    </script>
</body>
</html>
