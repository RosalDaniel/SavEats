<div class="stats-grid">
    @foreach ($stats as $stat)
        <div class="stat-card {{ $stat['type'] ?? '' }}">
            <h3>{{ $stat['value'] }}</h3>
            <p>{{ $stat['label'] }}</p>
        </div>
    @endforeach
</div>
