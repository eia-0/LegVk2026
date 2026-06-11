@props(['banners'])

@if($banners->count() > 0)
    <div x-data="{
        activeSlide: 0,
        slides: {{ $banners->count() }},
        intervalTime: {{ $banners->first()->interval ?? 0 }},
        autoPlay: {{ $banners->count() > 1 && $banners->first()->interval > 0 ? 'true' : 'false' }},
        init() {
            if (this.autoPlay) {
                setInterval(() => {
                    this.next();
                }, this.intervalTime * 1000);
            }
        },
        next() {
            this.activeSlide = (this.activeSlide + 1) % this.slides;
        }
    }" class="relative w-full overflow-hidden rounded-xl shadow" style="aspect-ratio: 1080 / 300;">
        @foreach($banners as $index => $banner)
            <div x-show="activeSlide === {{ $index }}" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-105"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 class="absolute inset-0">
                @if($banner->url)
                    <a href="{{ $banner->url }}" target="_blank" rel="noopener">
                        <img src="{{ asset('storage/' . $banner->image) }}" alt="Реклама" class="w-full h-full object-cover">
                    </a>
                @else
                    <img src="{{ asset('storage/' . $banner->image) }}" alt="Реклама" class="w-full h-full object-cover">
                @endif
            </div>
        @endforeach
    </div>
@endif