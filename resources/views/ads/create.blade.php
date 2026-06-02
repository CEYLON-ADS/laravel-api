@extends('layouts.app')

@section('content')
    <section class="panel" style="max-width: 760px; margin: 0 auto;">
        <h1 style="margin-top:0;">Publish Advertisement</h1>
        <p style="color: var(--muted);">This form replaces the previous SPA flow with server-rendered Blade.</p>
        <div class="chip" style="margin-bottom: .8rem;">Logged in as: {{ $loggedInMobile ?: 'Phone User' }}</div>

        <form method="POST" action="{{ route('ads.store') }}" enctype="multipart/form-data" style="display:grid; gap:0.9rem;">
            @csrf

            <div>
                <label for="title">Ad title</label>
                <input id="title" name="title" value="{{ old('title') }}" required>
                @error('title') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div>
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="5" required>{{ old('description') }}</textarea>
                @error('description') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div>
                <label for="images">Ad images (max 5, 2MB each)</label>
                <input id="images" name="images[]" type="file" accept="image/*" multiple>
                <div style="color:var(--muted); font-size:.85rem; margin-top:.35rem;">Images will be resized to around 100KB and uploaded to Cloudinary.</div>
                @error('images') <div class="error">{{ $message }}</div> @enderror
                @error('images.*') <div class="error">{{ $message }}</div> @enderror
            </div>
            <div>
                <label for="image_url">Ad image URL (optional)</label>
                <input id="image_url" name="image_url" value="{{ old('image_url') }}" placeholder="https://example.com/ad-image.jpg">
                @error('image_url') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div>
                <label for="listing_price">Listing price (LKR)</label>
                <input id="listing_price" name="listing_price" type="number" step="0.01" min="0" value="{{ old('listing_price') }}" placeholder="0.00">
                @error('listing_price') <div class="error">{{ $message }}</div> @enderror
            </div>
            <div>
                <label for="ad_tier">Ad type</label>
                <select id="ad_tier" name="ad_tier" required>
                    <option value="normal" @selected(old('ad_tier', 'normal') === 'normal')>Normal (2 days)</option>
                    <option value="super" @selected(old('ad_tier') === 'super')>Super (1 day top)</option>
                    <option value="vip" @selected(old('ad_tier') === 'vip')>VIP (1 day top)</option>
                </select>
                @error('ad_tier') <div class="error">{{ $message }}</div> @enderror
            </div>
            <div style="display:flex; gap:.6rem; align-items:center;">
                <label style="margin:0;">
                    <input type="checkbox" id="cashback" name="cashback" value="1" @checked(old('cashback'))>
                    CashBack
                </label>
            </div>

            <div style="display:grid; gap:0.9rem; grid-template-columns: 1fr;">
                <div>
                    <label for="contact_phone">Display contact phone</label>
                    <input id="contact_phone" name="contact_phone" value="{{ old('contact_phone') }}" required>
                    @error('contact_phone') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div style="display:grid; gap:0.9rem; grid-template-columns: 1fr 1fr;">
                <div>
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Select category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id') === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label>Cities (select multiple)</label>
                    <button type="button" id="openCities" class="btn ghost" style="width:100%; text-align:left;">Choose cities</button>
                    <div id="selectedCitiesPreview" style="margin-top:.5rem; color:var(--muted); font-size:.9rem;">
                        No cities selected
                    </div>
                    @error('cities') <div class="error">{{ $message }}</div> @enderror
                    @error('cities.*') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div style="display:flex; gap:1rem; flex-wrap:wrap;">
                <label><input type="checkbox" id="contact_whatsapp" name="contact_whatsapp" value="1" @checked(old('contact_whatsapp'))> WhatsApp</label>
                <label><input type="checkbox" id="telegram" name="telegram" value="1" @checked(old('telegram'))> Telegram</label>
                <label><input type="checkbox" id="imo" name="imo" value="1" @checked(old('imo'))> IMO</label>
                <label><input type="checkbox" id="viber" name="viber" value="1" @checked(old('viber'))> Viber</label>
            </div>

            <div id="socialFields" style="display:grid; gap:0.9rem;">
                <div id="field_contact_whatsapp" style="display:none;">
                    <label for="contact_whatsapp_number">WhatsApp number</label>
                    <input id="contact_whatsapp_number" name="contact_whatsapp_number" value="{{ old('contact_whatsapp_number') }}" placeholder="+9477XXXXXXX">
                    @error('contact_whatsapp_number') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div id="field_telegram" style="display:none;">
                    <label for="telegram_number">Telegram number</label>
                    <input id="telegram_number" name="telegram_number" value="{{ old('telegram_number') }}" placeholder="+9477XXXXXXX">
                    @error('telegram_number') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div id="field_imo" style="display:none;">
                    <label for="imo_number">IMO number</label>
                    <input id="imo_number" name="imo_number" value="{{ old('imo_number') }}" placeholder="+9477XXXXXXX">
                    @error('imo_number') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div id="field_viber" style="display:none;">
                    <label for="viber_number">Viber number</label>
                    <input id="viber_number" name="viber_number" value="{{ old('viber_number') }}" placeholder="+9477XXXXXXX">
                    @error('viber_number') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div style="display:flex; gap:0.7rem;">
                <button class="btn" type="submit">Submit Ad</button>
                <a class="btn ghost" href="{{ route('home') }}">Cancel</a>
            </div>
        </form>
    </section>

    <script>
        (function () {
            const map = [
                { checkbox: 'contact_whatsapp', field: 'field_contact_whatsapp', input: 'contact_whatsapp_number' },
                { checkbox: 'telegram', field: 'field_telegram', input: 'telegram_number' },
                { checkbox: 'imo', field: 'field_imo', input: 'imo_number' },
                { checkbox: 'viber', field: 'field_viber', input: 'viber_number' },
            ];

            const toggleField = (item) => {
                const checkbox = document.getElementById(item.checkbox);
                const field = document.getElementById(item.field);
                const input = document.getElementById(item.input);
                if (!checkbox || !field || !input) return;
                field.style.display = checkbox.checked ? 'block' : 'none';
                input.required = checkbox.checked;
            };

            map.forEach((item) => {
                const checkbox = document.getElementById(item.checkbox);
                if (!checkbox) return;
                checkbox.addEventListener('change', () => toggleField(item));
                toggleField(item);
            });
        })();
    </script>

    @php
        $selectedCities = (array) old('cities', []);
    @endphp

    <div id="citiesModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:50; align-items:center; justify-content:center; padding:1rem;">
        <div style="background:#fff; border-radius:16px; max-width:680px; width:100%; padding:1rem; box-shadow:0 18px 40px rgba(0,0,0,.2);">
            <div style="display:flex; justify-content:space-between; align-items:center; gap:.6rem; margin-bottom:.8rem;">
                <h3 style="margin:0;">Select Cities</h3>
                <div style="display:flex; gap:.5rem; align-items:center;">
                    <button type="button" id="toggleAllDistricts" class="btn ghost">Expand all</button>
                    <button type="button" id="closeCities" class="btn ghost">Close</button>
                </div>
            </div>
            <div style="display:grid; grid-template-columns: 1fr; gap:.6rem; border:1px solid #d7d2c8; border-radius:12px; padding:.75rem; max-height:360px; overflow:auto;">
                <label style="display:flex; align-items:center; justify-content:space-between; gap:.5rem; padding:.35rem .2rem; border:1px dashed #e2ddd5; border-radius:10px;">
                    <span style="font-weight:600;">All Island</span>
                    <input type="checkbox" id="selectAllIsland" style="margin:0;">
                </label>
                @forelse ($districts as $district)
                    @php
                        $hasSelected = false;
                        foreach ($district->cities as $city) {
                            if (in_array($city->id, $selectedCities, true)) {
                                $hasSelected = true;
                                break;
                            }
                        }
                    @endphp
                    <details style="border:1px solid #d6eadc; border-radius:12px; padding:.45rem .7rem; background:linear-gradient(90deg, rgba(23,163,74,0.08), rgba(23,163,74,0));" @if($hasSelected) open @endif>
                        <summary style="cursor:pointer; font-weight:700; color:#0f6f3c; list-style:none; display:flex; align-items:center; justify-content:space-between;">
                            <span>{{ $district->district }}</span>
                            <span style="font-size:.85rem; color:#1f8b52;">{{ $district->cities->count() }} cities</span>
                        </summary>
                        <div style="display:grid; grid-template-columns: 1fr; gap:.4rem; padding:.5rem 0 .2rem;">
                            @forelse ($district->cities as $city)
                                <label style="display:grid; grid-template-columns: 1fr 24px; align-items:center; gap:.5rem; min-height:28px;">
                                    <span style="line-height:1.2;">{{ $city->name }}</span>
                                    <input type="checkbox" name="cities[]" value="{{ $city->id }}" @checked(in_array($city->id, $selectedCities, true)) style="margin:0; justify-self:end;">
                                </label>
                            @empty
                                <div style="color:var(--muted); font-size:.9rem;">No cities added yet.</div>
                            @endforelse
                        </div>
                    </details>
                @empty
                    <div style="color:var(--muted); font-size:.95rem;">No districts available.</div>
                @endforelse
            </div>
            <div style="display:flex; justify-content:flex-end; margin-top:.8rem;">
                <button type="button" id="applyCities" class="btn">Apply</button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const modal = document.getElementById('citiesModal');
            const openBtn = document.getElementById('openCities');
            const closeBtn = document.getElementById('closeCities');
            const applyBtn = document.getElementById('applyCities');
            const preview = document.getElementById('selectedCitiesPreview');
            const toggleAllBtn = document.getElementById('toggleAllDistricts');
            const selectAllIsland = document.getElementById('selectAllIsland');
            if (!modal || !openBtn || !closeBtn || !applyBtn || !preview) return;

            const cityCheckboxes = () => Array.from(modal.querySelectorAll('input[name="cities[]"]'));

            const updatePreview = () => {
                const checked = modal.querySelectorAll('input[name="cities[]"]:checked');
                if (checked.length === 0) {
                    preview.textContent = 'No cities selected';
                    return;
                }
                const names = Array.from(checked).map((el) => el.nextElementSibling?.textContent || '').filter(Boolean);
                preview.textContent = names.join(', ');
            };

            const updateAllIslandState = () => {
                if (!selectAllIsland) return;
                const cities = cityCheckboxes();
                if (cities.length === 0) return;
                const allChecked = cities.every((el) => el.checked);
                const anyChecked = cities.some((el) => el.checked);
                selectAllIsland.checked = allChecked;
                selectAllIsland.indeterminate = anyChecked && !allChecked;
            };

            const openModal = () => {
                modal.style.display = 'flex';
                updatePreview();
                updateToggleLabel();
                updateAllIslandState();
            };
            const closeModal = () => {
                modal.style.display = 'none';
            };

            const updateToggleLabel = () => {
                if (!toggleAllBtn) return;
                const details = modal.querySelectorAll('details');
                if (details.length === 0) return;
                const allOpen = Array.from(details).every((el) => el.open);
                toggleAllBtn.textContent = allOpen ? 'Collapse all' : 'Expand all';
            };

            const toggleAll = () => {
                const details = modal.querySelectorAll('details');
                if (details.length === 0) return;
                const allOpen = Array.from(details).every((el) => el.open);
                details.forEach((el) => {
                    el.open = !allOpen;
                });
                updateToggleLabel();
            };

            openBtn.addEventListener('click', openModal);
            closeBtn.addEventListener('click', closeModal);
            applyBtn.addEventListener('click', () => {
                updatePreview();
                closeModal();
            });
            if (toggleAllBtn) {
                toggleAllBtn.addEventListener('click', toggleAll);
            }
            if (selectAllIsland) {
                selectAllIsland.addEventListener('change', () => {
                    const cities = cityCheckboxes();
                    cities.forEach((el) => {
                        el.checked = selectAllIsland.checked;
                    });
                    updatePreview();
                    updateAllIslandState();
                });
            }

            modal.addEventListener('click', (event) => {
                if (event.target === modal) closeModal();
            });
            modal.addEventListener('toggle', updateToggleLabel, true);
            modal.addEventListener('change', (event) => {
                if (event.target && event.target.matches('input[name="cities[]"]')) {
                    updateAllIslandState();
                }
            });

            updatePreview();
            updateAllIslandState();
        })();
    </script>
@endsection
