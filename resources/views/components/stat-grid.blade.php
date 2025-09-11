<div class="stats-grid">
    @foreach ($stats as $stat)
        <div class="stat-card {{ $stat['type'] ?? '' }}">
            <p>{{ $stat['label'] }}</p>
            <h3>{{ $stat['value'] }}</h3>
        </div>
    @endforeach
</div>

