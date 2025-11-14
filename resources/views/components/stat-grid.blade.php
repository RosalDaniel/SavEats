<div class="stats-grid">
    @foreach ($stats as $stat)
        <div class="stat-card {{ $stat['type'] ?? '' }}">
            <p class="stat-label">{{ $stat['label'] }}</p>
            <h3 class="stat-value">{{ $stat['value'] }}</h3>
        </div>
    @endforeach
</div>

