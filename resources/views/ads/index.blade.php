@extends('layouts.app')

@section('content')
    <style>
        .listing-layout {
            display: grid;
            grid-template-columns: 290px 1fr;
            gap: 1.1rem;
            align-items: start;
        }
        .filter-shell {
            position: sticky;
            top: 1rem;
            background:
                linear-gradient(155deg, rgba(32, 101, 142, 0.96), rgba(42, 128, 180, 0.92) 52%, rgba(115, 166, 198, 0.88));
            border: 1px solid rgba(191, 220, 238, 0.62);
            border-radius: 22px;
            box-shadow: 0 24px 34px rgba(26, 79, 111, 0.32), inset 0 1px 0 rgba(255, 255, 255, 0.2);
            color: #f4faff;
            overflow: hidden;
        }
        .filter-head {
            padding: 1rem 1rem .75rem;
            border-bottom: 1px solid rgba(227, 242, 252, 0.32);
            background: linear-gradient(120deg, rgba(255, 255, 255, 0.07), rgba(255, 255, 255, 0.01));
        }
        .filter-pill {
            display: inline-block;
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            font-weight: 700;
            border: 1px solid rgba(219, 238, 251, 0.52);
            border-radius: 999px;
            padding: .22rem .58rem;
            margin-bottom: .55rem;
            background: rgba(230, 244, 253, 0.12);
        }
        .filter-title {
            margin: 0 0 .2rem;
            font-size: 1.2rem;
            line-height: 1.15;
            color: #f5fbff;
        }
        .filter-sub {
            margin: 0;
            color: rgba(231, 245, 255, 0.9);
            font-size: .87rem;
        }
        .filter-body { padding: .9rem 1rem 1rem; }
        .filter-stack { display: grid; gap: .8rem; }
        .field-wrap {
            background: rgba(21, 74, 105, 0.28);
            border: 1px solid rgba(223, 241, 252, 0.28);
            border-radius: 14px;
            padding: .6rem .64rem;
        }
        .field-wrap label {
            color: #eaf7ff;
            font-size: .8rem;
            letter-spacing: .02em;
            margin-bottom: .35rem;
            display: block;
        }
        .filter-shell input,
        .filter-shell select {
            border-radius: 10px;
            border: 1px solid rgba(182, 216, 238, 0.75);
            background: rgba(246, 251, 255, 0.97);
            color: #1b4f72;
            padding: .56rem .62rem;
        }
        .filter-shell input:focus,
        .filter-shell select:focus {
            outline: none;
            border-color: rgba(115, 166, 198, 1);
            box-shadow: 0 0 0 3px rgba(115, 166, 198, 0.3);
        }
        .filter-actions { display: grid; grid-template-columns: 1fr 1fr; gap: .55rem; margin-top: .2rem; }
        .filter-actions .btn {
            text-align: center;
            font-weight: 700;
            border-radius: 11px;
        }
        .filter-actions .btn.ghost {
            background: rgba(246, 252, 255, 0.96);
            border-color: rgba(184, 218, 239, 0.76);
        }
        .range-readout {
            margin-top: .35rem;
            font-size: .78rem;
            color: #e9f7ff;
            font-weight: 600;
        }
        .range-readout.price {
            display: flex;
            justify-content: space-between;
            gap: .4rem;
            flex-wrap: wrap;
        }
        .top-search {
            display: flex;
            gap: .6rem;
            align-items: center;
            margin-bottom: 1rem;
            padding: .8rem 1rem;
            background: linear-gradient(135deg, #f7fbff, #ecf5fc);
            border: 1px solid #c8deed;
            border-radius: 16px;
            box-shadow: 0 8px 18px rgba(35, 108, 152, 0.11);
        }
        .top-search input {
            flex: 1;
            border-radius: 12px;
            border: 1px solid #c8deed;
            background: #ffffff;
            padding: .7rem .8rem;
            font: inherit;
        }
        .top-search small {
            color: var(--muted);
            font-size: .85rem;
        }
        .result-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .6rem;
            margin-bottom: .8rem;
        }
        .result-meta {
            color: var(--muted);
            font-size: .9rem;
        }
        @media (max-width: 930px) {
            .listing-layout { grid-template-columns: 1fr; }
        }
    </style>

    <form id="liveSearchForm" method="GET" action="{{ route('home') }}" class="top-search">
        <input id="top_search" name="q" value="{{ request('q') }}" placeholder="Search ads...">
       
        <input type="hidden" name="category" id="live_category" value="{{ request('category') }}">
        <span id="live_cities_container"></span>
        <input type="hidden" name="location" id="live_location" value="{{ request('location') }}">
        <input type="hidden" name="days_back" id="live_days_back" value="{{ request('days_back', 0) }}">
        <input type="hidden" name="min_price" id="live_min_price" value="{{ request('min_price', 0) }}">
        <input type="hidden" name="max_price" id="live_max_price" value="{{ request('max_price', 100000) }}">
    </form>

    <div class="listing-layout">
        <aside class="filter-shell">
            <div class="filter-head">
                <h2 class="filter-title">Filter Ads</h2>
                <p class="filter-sub">Category, location, date, and price range</p>
            </div>
            <div class="filter-body">
            <form method="GET" action="{{ route('home') }}" class="filter-stack">
                <div class="field-wrap">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category') === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field-wrap">
                    <label for="cities">Locations (multi-select)</label>
                    <select id="cities" name="cities[]" multiple size="5">
                        @foreach ($cities as $city)
                            <option value="{{ $city->id }}" @selected(in_array($city->id, (array) request('cities', []), true))>{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field-wrap">
                    <label for="location">Location text</label>
                    <input id="location" name="location" value="{{ request('location') }}" placeholder="e.g. Colombo">
                </div>

                <div class="field-wrap">
                    <label for="days_back">Date range bar (last N days)</label>
                    <input type="range" id="days_back" name="days_back" min="0" max="365" step="1" value="{{ request('days_back', 0) }}">
                    <div class="range-readout" id="daysBackReadout">
                        {{ (int) request('days_back', 0) === 0 ? 'Any time' : 'Last '.(int) request('days_back', 0).' days' }}
                    </div>
                </div>

                <div class="field-wrap">
                    <label for="min_price">Min price bar</label>
                    <input type="range" id="min_price" name="min_price" min="0" max="100000" step="100" value="{{ request('min_price', 0) }}">
                    <div class="range-readout" id="minPriceReadout">LKR {{ number_format((int) request('min_price', 0)) }}</div>
                </div>

                <div class="field-wrap">
                    <label for="max_price">Max price bar</label>
                    <input type="range" id="max_price" name="max_price" min="0" max="100000" step="100" value="{{ request('max_price', 100000) }}">
                    <div class="range-readout" id="maxPriceReadout">LKR {{ number_format((int) request('max_price', 100000)) }}</div>
                </div>

                <div class="field-wrap">
                    <label>Selected price range</label>
                    <div class="range-readout price" id="priceRangeReadout">
                        <span>Min: LKR {{ number_format((int) request('min_price', 0)) }}</span>
                        <span>Max: LKR {{ number_format((int) request('max_price', 100000)) }}</span>
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn">Apply</button>
                    <a class="btn ghost" href="{{ route('home') }}">Reset</a>
                </div>
            </form>
            </div>
        </aside>

        <section>
            <div class="result-head">
                <div>
                    <h1 style="margin: 0 0 0.15rem;">Find New Ads</h1>
                    <div class="result-meta">Showing {{ $ads->count() }} of {{ $ads->total() }} ads</div>
                </div>
            </div>

            <section class="grid">
                @forelse ($ads as $ad)
                    <article class="panel">
                        @php
                            $adImages = (array) ($ad->image_urls ?? []);
                            $adImage = $adImages[0] ?? $ad->image_url;
                        @endphp
                        @if ($adImage)
                            <img src="{{ $adImage }}" alt="{{ $ad->title }}" class="ad-image">
                        @else
                            <div class="ad-image-placeholder">No Image</div>
                        @endif
                        <div style="height:0.7rem;"></div>
                        <div style="display:flex; justify-content:space-between; align-items:center; gap:0.5rem;">
                            <h3 style="margin: 0;">{{ $ad->title }}</h3>
                            <div style="display:flex; gap:.35rem; align-items:center; flex-wrap:wrap;">
                                @if($ad->is_pinned)
                                    <span class="chip" style="background:#e6f3fb; border-color:#9fc9e4; color:#205e86;">Pinned</span>
                                @endif
                                <span class="chip" style="background:#edf6fc; border-color:#a9cee6; color:#1f628d;">
                                    {{ $ad->advertiseType?->price !== null ? 'LKR '.number_format((float)$ad->advertiseType->price, 2) : 'Price N/A' }}
                                </span>
                                @if ($ad->cashback)
                                    <span class="chip" style="background:#fff7e6; border-color:#f1c57a; color:#8a5a10;">CashBack</span>
                                @endif
                                <span class="chip">{{ $ad->category?->name ?? 'General' }}</span>
                            </div>
                        </div>
                        <p style="color: var(--muted); min-height: 55px;">{{ \Illuminate\Support\Str::limit($ad->description, 130) }}</p>
                        <p style="margin: 0 0 0.5rem; font-size: 0.9rem;">
                            <strong>City:</strong> {{ $ad->city?->name ?? 'Not specified' }}
                        </p>
                        <p style="margin: 0 0 0.8rem; font-size: 0.9rem;">
                            <strong>Price:</strong> {{ $ad->advertiseType?->price !== null ? 'LKR '.number_format((float)$ad->advertiseType->price, 2) : 'N/A' }}
                            @if ($ad->cashback)
                                <span style="margin-left:.4rem; font-weight:700; color:#8a5a10;">CashBack</span>
                            @endif
                        </p>
                        <p style="margin: 0 0 0.8rem; font-size: 0.9rem;">
                            <strong>Views:</strong> {{ number_format((int) $ad->views_count) }}
                        </p>
                        <p style="margin: 0 0 0.8rem; font-size: 0.9rem;">
                            <strong>Likes:</strong> {{ number_format((int) $ad->likes_count) }}
                        </p>
                        <a class="btn ghost" href="{{ route('ads.show', $ad) }}">View Details</a>
                    </article>
                @empty
                    <article class="panel">
                        <h3 style="margin-top:0;">No ads found</h3>
                        <p style="color: var(--muted);">Try changing one or more filters.</p>
                        <a href="{{ route('home') }}" class="btn">Clear Filters</a>
                    </article>
                @endforelse
            </section>

            <div style="margin-top: 1rem;">
                {{ $ads->links() }}
            </div>
        </section>
    </div>

    <script>
        (function () {
            const range = document.getElementById('days_back');
            const readout = document.getElementById('daysBackReadout');
            if (!range || !readout) return;

            const render = () => {
                const value = parseInt(range.value || '0', 10);
                readout.textContent = value === 0 ? 'Any time' : `Last ${value} days`;
            };

            range.addEventListener('input', render);
            render();
        })();

        (function () {
            const minInput = document.getElementById('min_price');
            const maxInput = document.getElementById('max_price');
            const minReadout = document.getElementById('minPriceReadout');
            const maxReadout = document.getElementById('maxPriceReadout');
            const priceRangeReadout = document.getElementById('priceRangeReadout');
            if (!minInput || !maxInput || !minReadout || !maxReadout || !priceRangeReadout) return;

            const formatLkr = (n) => `LKR ${Number(n || 0).toLocaleString()}`;

            const renderPrice = () => {
                let minVal = parseInt(minInput.value || '0', 10);
                let maxVal = parseInt(maxInput.value || '0', 10);
                if (minVal > maxVal) {
                    [minVal, maxVal] = [maxVal, minVal];
                    minInput.value = String(minVal);
                    maxInput.value = String(maxVal);
                }

                minReadout.textContent = formatLkr(minVal);
                maxReadout.textContent = formatLkr(maxVal);
                priceRangeReadout.innerHTML = `<span>Min: ${formatLkr(minVal)}</span><span>Max: ${formatLkr(maxVal)}</span>`;
            };

            minInput.addEventListener('input', renderPrice);
            maxInput.addEventListener('input', renderPrice);
            renderPrice();
        })();

        (function () {
            const topSearch = document.getElementById('top_search');
            const form = document.getElementById('liveSearchForm');
            if (!topSearch || !form) return;

            const map = [
                ['category', 'live_category'],
                ['location', 'live_location'],
                ['days_back', 'live_days_back'],
                ['min_price', 'live_min_price'],
                ['max_price', 'live_max_price'],
            ];

            const syncFilters = () => {
                map.forEach(([sourceId, targetId]) => {
                    const source = document.getElementById(sourceId);
                    const target = document.getElementById(targetId);
                    if (source && target) {
                        target.value = source.value;
                    }
                });

                const citiesSelect = document.getElementById('cities');
                const container = document.getElementById('live_cities_container');
                if (citiesSelect && container) {
                    container.innerHTML = '';
                    Array.from(citiesSelect.selectedOptions).forEach((opt) => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'cities[]';
                        input.value = opt.value;
                        container.appendChild(input);
                    });
                }
            };

            let timer = null;
            topSearch.addEventListener('input', () => {
                syncFilters();
                if (timer) clearTimeout(timer);
                timer = setTimeout(() => form.submit(), 350);
            });

            ['category','cities','location','days_back','min_price','max_price'].forEach((id) => {
                const el = document.getElementById(id);
                if (!el) return;
                el.addEventListener('change', syncFilters);
                el.addEventListener('input', syncFilters);
            });
        })();
    </script>
@endsection
