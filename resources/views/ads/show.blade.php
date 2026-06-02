@extends('layouts.app')

@section('content')
    <style>
        .ad-shell {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(0, .9fr);
            gap: 1.2rem;
        }
        .ad-hero {
            border-radius: 18px;
            border: 1px solid #cfe9dd;
            background: linear-gradient(180deg, #ffffff, #f5fdf9);
            padding: 1rem;
            box-shadow: 0 18px 30px rgba(11, 102, 71, 0.12);
        }
        .ad-side {
            position: sticky;
            top: 1rem;
            align-self: start;
            border-radius: 18px;
            border: 1px solid #cfe9dd;
            background: linear-gradient(180deg, #ffffff, #f3fbf7);
            padding: 1rem;
            box-shadow: 0 18px 30px rgba(11, 102, 71, 0.12);
        }
        .ad-title { margin: 0; }
        .ad-meta { display:flex; gap:.4rem; align-items:center; flex-wrap:wrap; }
        .ad-card {
            border: 1px solid #d8ece3;
            border-radius: 14px;
            padding: .75rem .85rem;
            background: #ffffff;
            box-shadow: 0 10px 16px rgba(11, 102, 71, 0.08);
        }
        .ad-grid { display:grid; grid-template-columns: 1fr 1fr; gap:.7rem; }
        .ad-price {
            font-size: 1.35rem;
            font-weight: 700;
            color: #0e6c3f;
        }
        .ad-detail-row { display:flex; justify-content:space-between; gap:.5rem; font-size:.95rem; color:var(--muted); }
        .ad-actions { display:flex; gap:.6rem; flex-wrap:wrap; margin-top:.8rem; }
        @media (max-width: 980px) {
            .ad-shell { grid-template-columns: 1fr; }
            .ad-side { position: static; }
        }
    </style>

    @php
        $adImages = (array) ($advertisement->image_urls ?? []);
        $adImage = $adImages[0] ?? $advertisement->image_url;
    @endphp

    <article class="panel" style="max-width: 1100px; margin: 0 auto;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.75rem; flex-wrap:wrap; margin-bottom:1rem;">
            <div>
                <h1 class="ad-title">{{ $advertisement->title }}</h1>
                <div style="color:var(--muted); margin-top:.25rem;">
                    {{ $advertisement->category?->name ?? 'General' }} · {{ $advertisement->city?->name ?? 'Not specified' }}
                </div>
            </div>
            <div class="ad-meta">
                @if($advertisement->is_pinned)
                    <span class="chip" style="background:#d7f7ea; border-color:#6cd4aa; color:#0b6d48;">Pinned</span>
                @endif
                <span class="chip">{{ strtoupper($advertisement->status) }}</span>
                @if ($advertisement->cashback)
                    <span class="chip" style="background:#fff3d7; border-color:#f2d08b; color:#8a5a10;">CashBack</span>
                @endif
            </div>
        </div>

        <div class="ad-shell">
            <div class="ad-hero">
                <div>
                    @if ($adImage)
                        <img src="{{ $adImage }}" alt="{{ $advertisement->title }}" class="ad-image" style="height:320px;">
                    @else
                        <div class="ad-image-placeholder" style="height:320px;">No Image</div>
                    @endif
                    @if (count($adImages) > 1)
                        <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap:.5rem; margin-top:.8rem;">
                            @foreach (array_slice($adImages, 1) as $image)
                                <img src="{{ $image }}" alt="Ad image" style="width:100%; height:90px; object-fit:cover; border-radius:10px; border:1px solid #bde8d4;">
                            @endforeach
                        </div>
                    @endif
                </div>

                <div style="margin-top:1rem;">
                    <h3 style="margin:.2rem 0 .5rem;">About this ad</h3>
                    <p style="color:var(--muted); margin:0;">{{ $advertisement->description }}</p>
                </div>

                <div class="ad-actions">
                    <form method="POST" action="{{ route('ads.like', $advertisement) }}">
                        @csrf
                        <button class="btn {{ $liked ? 'muted' : '' }}" type="submit">
                            {{ $liked ? 'Liked' : 'Like this ad' }}
                        </button>
                    </form>
                    <a class="btn ghost" href="{{ route('home') }}">Back to Listings</a>
                </div>
            </div>

            <aside class="ad-side">
                <div class="ad-card" style="margin-bottom:.8rem;">
                    <div style="color:var(--muted); font-size:.9rem;">Price</div>
                    <div class="ad-price">
                        {{ $advertisement->advertiseType?->price !== null ? 'LKR '.number_format((float)$advertisement->advertiseType->price, 2) : 'N/A' }}
                    </div>
                </div>

                <div class="ad-grid" style="margin-bottom:.8rem;">
                    <div class="ad-card">
                        <div style="color:var(--muted); font-size:.85rem;">Views</div>
                        <div style="font-weight:700;">{{ number_format((int) $advertisement->views_count) }}</div>
                    </div>
                    <div class="ad-card">
                        <div style="color:var(--muted); font-size:.85rem;">Likes</div>
                        <div style="font-weight:700;">{{ number_format((int) $advertisement->likes_count) }}</div>
                    </div>
                    <div class="ad-card">
                        <div style="color:var(--muted); font-size:.85rem;">Published By</div>
                        <div style="font-weight:700;">{{ $advertisement->user?->mobile_number ?? '-' }}</div>
                    </div>
                    <div class="ad-card">
                        <div style="color:var(--muted); font-size:.85rem;">Contact</div>
                        <div style="font-weight:700;">{{ $advertisement->contact_phone }}</div>
                    </div>
                </div>

                <div class="ad-card">
                    <div class="ad-detail-row"><span>Category</span><strong>{{ $advertisement->category?->name ?? 'General' }}</strong></div>
                    <div class="ad-detail-row" style="margin-top:.35rem;"><span>City</span><strong>{{ $advertisement->city?->name ?? 'Not specified' }}</strong></div>
                </div>
            </aside>
        </div>

        @php
            $sanitize = fn ($value) => preg_replace('/\D+/', '', (string) $value);
            $waNumber = $sanitize($advertisement->contact_whatsapp_number ?: $advertisement->contact_phone);
            $tgNumber = $sanitize($advertisement->telegram_number ?: $advertisement->contact_phone);
            $imoNumber = $sanitize($advertisement->imo_number ?: $advertisement->contact_phone);
            $viberNumber = $sanitize($advertisement->viber_number ?: $advertisement->contact_phone);
        @endphp

        <style>
            .social-btn {
                display: inline-flex;
                align-items: center;
                gap: .55rem;
                text-decoration: none;
                font-size: 1rem;
                padding: .45rem .8rem;
                border-radius: 999px;
                border: 1px solid #cfe9dd;
                background: linear-gradient(135deg, #ffffff, #f0fbf6);
                box-shadow: 0 6px 14px rgba(11, 102, 71, 0.12);
                color: #123c2f;
                font-weight: 700;
            }
            .social-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 34px;
                height: 34px;
                border-radius: 50%;
                color: #fff;
                box-shadow: 0 4px 10px rgba(0,0,0,0.12);
            }
            .social-whatsapp { background: linear-gradient(135deg, #25d366, #128c7e); }
            .social-telegram { background: linear-gradient(135deg, #2aabee, #0b7dbf); }
            .social-imo { background: linear-gradient(135deg, #3aa6ff, #1f6edc); }
            .social-viber { background: linear-gradient(135deg, #8f5be8, #6f2bd8); }
            .social-icon svg { width: 18px; height: 18px; }
        </style>

        <div style="display:flex; gap:0.9rem; flex-wrap:wrap; margin-bottom:1rem;">
            @if ($advertisement->contact_whatsapp)
                <a class="social-btn" target="_blank" rel="noopener" href="https://wa.me/{{ $waNumber }}">
                    <span class="social-icon social-whatsapp">
                        <svg viewBox="0 0 32 32" fill="currentColor"><path d="M19.11 17.36c-.2-.1-1.18-.58-1.36-.65-.18-.07-.31-.1-.44.1-.13.2-.5.65-.62.78-.11.13-.22.15-.42.05-.2-.1-.85-.31-1.62-.99-.6-.53-1-1.18-1.12-1.38-.12-.2-.01-.31.09-.41.09-.09.2-.23.3-.34.1-.11.13-.19.2-.31.07-.13.04-.24-.02-.34-.06-.1-.44-1.07-.6-1.46-.16-.38-.33-.33-.44-.33-.11 0-.24-.01-.37-.01-.13 0-.34.05-.52.24-.18.2-.68.66-.68 1.6 0 .94.7 1.85.8 1.98.1.13 1.37 2.09 3.33 2.93.47.2.83.32 1.11.41.47.15.9.13 1.24.08.38-.06 1.18-.48 1.35-.94.17-.46.17-.85.12-.94-.05-.08-.18-.13-.38-.23z"/><path d="M26.67 5.33C23.82 2.48 20.01.91 16 .91 7.98.91 1.47 7.42 1.47 15.44c0 2.56.67 5.06 1.94 7.26L1.22 30.1l7.56-2.18c2.09 1.14 4.44 1.75 6.86 1.75h.01c8.02 0 14.53-6.51 14.53-14.53 0-4.01-1.57-7.82-4.51-10.81zM16 27.01h-.01c-2.18 0-4.31-.58-6.16-1.68l-.44-.26-4.48 1.3 1.2-4.37-.29-.45c-1.2-1.9-1.84-4.1-1.84-6.38 0-6.59 5.36-11.95 11.96-11.95 3.19 0 6.19 1.24 8.45 3.5 2.26 2.26 3.5 5.26 3.5 8.45 0 6.59-5.36 11.95-11.95 11.95z"/></svg>
                    </span>
                    WhatsApp
                </a>
            @endif
            @if ($advertisement->telegram)
                <a class="social-btn" target="_blank" rel="noopener" href="https://t.me/{{ $tgNumber }}">
                    <span class="social-icon social-telegram">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M9.04 15.47 8.9 19c.57 0 .82-.24 1.12-.53l2.7-2.56 5.6 4.1c1.03.57 1.76.27 2.03-.95l3.68-17.26h0c.34-1.56-.56-2.18-1.56-1.81L1.34 9.54C-.13 10.12-.11 10.93 1.05 11.3l4.58 1.43 10.62-6.7c.5-.33.96-.15.58.18z"/></svg>
                    </span>
                    Telegram
                </a>
            @endif
            @if ($advertisement->imo)
                <a class="social-btn" target="_blank" rel="noopener" href="imo://chat?phone={{ $imoNumber }}">
                    <span class="social-icon social-imo">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.07 2 11.1c0 2.86 1.4 5.4 3.6 7.07L5 22l3.9-2.05c.98.27 2.03.42 3.1.42 5.52 0 10-4.07 10-9.1S17.52 2 12 2z"/></svg>
                    </span>
                    IMO
                </a>
            @endif
            @if ($advertisement->viber)
                <a class="social-btn" href="viber://chat?number={{ $viberNumber }}">
                    <span class="social-icon social-viber">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16.53 14.61c-.2-.1-1.18-.58-1.36-.65-.18-.07-.31-.1-.44.1-.13.2-.5.65-.62.78-.11.13-.22.15-.42.05-.2-.1-.85-.31-1.62-.99-.6-.53-1-1.18-1.12-1.38-.12-.2-.01-.31.09-.41.09-.09.2-.23.3-.34.1-.11.13-.19.2-.31.07-.13.04-.24-.02-.34-.06-.1-.44-1.07-.6-1.46-.16-.38-.33-.33-.44-.33-.11 0-.24-.01-.37-.01-.13 0-.34.05-.52.24-.18.2-.68.66-.68 1.6 0 .94.7 1.85.8 1.98.1.13 1.37 2.09 3.33 2.93.47.2.83.32 1.11.41.47.15.9.13 1.24.08.38-.06 1.18-.48 1.35-.94.17-.46.17-.85.12-.94-.05-.08-.18-.13-.38-.23z"/><path d="M12 2c-5.52 0-10 4.07-10 9.1 0 2.86 1.4 5.4 3.6 7.07L5 22l3.9-2.05c.98.27 2.03.42 3.1.42 5.52 0 10-4.07 10-9.1S17.52 2 12 2z"/></svg>
                    </span>
                    Viber
                </a>
            @endif
        </div>

    </article>
@endsection
