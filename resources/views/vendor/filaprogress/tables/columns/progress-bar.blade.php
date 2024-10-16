@php
    $total = $getState()['total'];
    $progress = $getState()['progress'];
    $progress = $total > 0 ? ($progress / $total) * 100 : 0;

    if($progress == 100){
        $progressColor = '#27ae60';
    } else if($progress > 50){
        $progressColor = '#2980b9';
    } else if($progress > 25){
        $progressColor = '#f39c12';
    } else {
        $progressColor = '#e74c3c';
    }

    $displayProgress = $progress == 100 ? number_format($progress, 0) : number_format($progress, 2);
@endphp

<div class="progress-container">
    <div class="progress-bar" style="width: {{ $displayProgress }}%; background-color: {{ $progressColor }};"></div>
    <div class="progress-text">
        @if($column instanceof \IbrahimBougaoua\FilaProgress\Tables\Columns\ProgressBar && $column->getCanShow())
            <small @class([
                'text-gray-700' => $displayProgress != 100,
                'text-white' => $displayProgress == 100
                ])>
                {{ $displayProgress }}%
            </small>
        @endif
    </div>
</div>

<style>
    .progress-container {
        width: 100%;
        background-color: #667084;
        border-radius: 0.375rem;
        height: 0.3rem;
        overflow: hidden;
        position: relative;
        box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.1);
    }
    .progress-bar {
        height: 100%;
        border-radius: 0.2rem;
        width: 0;
    }

</style>
