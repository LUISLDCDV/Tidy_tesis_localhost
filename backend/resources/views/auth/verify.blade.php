@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-envelope-open-text"></i> {{ __('Verify Your Email Address') }}
                </div>

                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle"></i> {{ __('A fresh verification link has been sent to your email address.') }}
                        </div>
                    @endif

                    <p><i class="fas fa-info-circle"></i> {{ __('Before proceeding, please check your email for a verification link.') }}</p>
                    <p>{{ __('If you did not receive the email') }},
                    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <button type="submit" class="btn btn-link p-0 m-0 align-baseline">
                            <i class="fas fa-redo"></i> {{ __('click here to request another') }}
                        </button>.
                    </form></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
