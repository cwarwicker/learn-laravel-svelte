@extends('layouts.example')

@section('content')

    <h1>Proof of concept assignment</h1>
    <p>This is a hard-coded set of questions with hard-coded logic behind them, but obviously, in a real system this would all be configurable by the teacher.</p>

    <form action="{{ route('submit') }}" method="post">
        @csrf

        <input type="hidden" name="user_id" value="{{ $user_id }}">

        <h3>1. What colour is the sky?</h3>
        <div class="form-group">
            <select name="q1" class="form-control">
                <option value=""></option>
                <option value="blue">Blue</option>
                <option value="red">Red</option>
                <option value="green">Green</option>
            </select>
        </div>

        <h3>2. True or False - The sky is blue?</h3>
        <div class="form-check-inline form-check">
            <input class="form-check-input" type="radio" name="q2" value="0">
            <label class="form-check-label">False</label>
        </div>
        <div class="form-check-inline form-check">
            <input class="form-check-input" type="radio" name="q2" value="1">
            <label class="form-check-label">True</label>
        </div>

        <h3>3. Do you want an extra point?</h3>
        <div class="form-check-inline form-check">
            <input class="form-check-input" type="checkbox" name="q3" value="1">
            <label class="form-check-label">Yes</label>
        </div>

        <p><input type="submit" class="btn btn-primary"></p>


    </form>

@endsection
