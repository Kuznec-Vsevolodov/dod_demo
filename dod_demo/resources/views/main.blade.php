@extends('layouts.app')

@section('content')
    <div class="container">
        <!-- Вывод компонента для входа в чат  -->
        <section class="content-section row justify-content-center container">
        @if($_SERVER['REQUEST_URI'] == '/')
            <invite-component :user="{{Auth::user()}}"></invite-component>
        @elseif(preg_match('/chat/i', $_SERVER['REQUEST_URI']))
            <chat-component :chat_channel_id="{{$id}}" :user="{{Auth::user()}}"></chat-component>    
        @endif
        <!-- Вывод компонента чата  -->
        @if(Auth::check())
            <sidebar-component :user_data='{{Auth::user()}}'>
        @else
        <!-- Вывод компонента при отсутствии авторизации  -->
            <empty-sidebar-component></empty-sidebar-component>
        @endif    
        </section>
        <!-- Кнопка входа в уже вошедший чат  -->
        @if($_SERVER['REQUEST_URI'] == '/')    
        <div class="current-chat-link">
            @if(Auth::check())
                @if(Auth::user()->current_chat != 0)
                    <a href="/chat/{{Auth::user()->current_chat}}">Ваш текущий чат</a>
                @endif
            @endif
        </div>
        @endif
    </div>    
@endsection