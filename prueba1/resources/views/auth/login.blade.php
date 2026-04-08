<x-guest-layout>
    <div class="min-h-screen bg-[#0E0E12] flex">

        {{-- ── Panel decorativo izquierdo ── --}}
        <div class="hidden lg:flex flex-1 relative flex-col justify-end p-12 bg-[#16161C] overflow-hidden">
            <div class="absolute -top-24 -right-36 w-[480px] h-[480px] rounded-full"
                 style="background:radial-gradient(circle,rgba(212,175,83,.13) 0%,transparent 70%)"></div>
            <div class="absolute bottom-20 -left-16 w-[280px] h-[280px] rounded-full"
                 style="background:radial-gradient(circle,rgba(212,175,83,.08) 0%,transparent 70%)"></div>
            <div class="absolute inset-0"
                 style="background-image:repeating-linear-gradient(-45deg,transparent,transparent 36px,rgba(255,255,255,.012) 36px,rgba(255,255,255,.012) 37px)"></div>

            <span class="absolute top-10 left-12 font-serif text-2xl tracking-wide text-[#D4AF53]">
                {{ config('app.name') }}
            </span>

            <div class="relative z-10">
                <blockquote class="font-serif italic text-3xl text-[#F0EAD6] leading-snug max-w-md mb-3">
                    "El estilo es saber quién eres, qué quieres decir y no dar un centavo por lo que otros piensan."
                </blockquote>
                <cite class="text-xs uppercase tracking-widest text-[#F0EAD6]/40 not-italic">— Orson Welles</cite>
            </div>
        </div>

        {{-- ── Panel formulario ── --}}
        <div class="flex flex-col justify-center items-center w-full lg:w-[480px] px-8 py-12 bg-[#16161C] border-l border-white/[0.06]">
            <div class="w-full max-w-sm">

                <h1 class="font-serif text-[2rem] font-normal text-[#F0EAD6] leading-tight mb-2">Bienvenido</h1>
                <div class="w-10 h-0.5 mb-6" style="background:linear-gradient(90deg,#D4AF53,transparent)"></div>
                <p class="text-sm text-[#F0EAD6]/45 mb-8 leading-relaxed">Inicia sesión en tu cuenta para continuar.</p>

                @if (session('status'))
                    <div class="mb-5 text-sm text-[#6DBFA0] bg-[#6DBFA0]/10 border border-[#6DBFA0]/25 rounded-md px-4 py-3">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-5">
                        <label for="email" class="block text-[0.7rem] uppercase tracking-widest text-[#F0EAD6]/45 mb-2">Correo electrónico</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="tu@correo.com"
                            class="w-full bg-[#252530] border border-white/[0.08] rounded-md text-[#F0EAD6] text-sm font-light px-4 py-3 outline-none placeholder-[#F0EAD6]/20 focus:border-[#D4AF53]/60 transition-colors" />
                        @error('email') <p class="mt-1.5 text-xs text-[#E07070]">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-6">
                        <label for="password" class="block text-[0.7rem] uppercase tracking-widest text-[#F0EAD6]/45 mb-2">Contraseña</label>
                        <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••"
                            class="w-full bg-[#252530] border border-white/[0.08] rounded-md text-[#F0EAD6] text-sm font-light px-4 py-3 outline-none placeholder-[#F0EAD6]/20 focus:border-[#D4AF53]/60 transition-colors" />
                        @error('password') <p class="mt-1.5 text-xs text-[#E07070]">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-between mb-6">
                        <label for="remember_me" class="flex items-center gap-2 cursor-pointer">
                            <input id="remember_me" type="checkbox" name="remember"
                                class="w-4 h-4 rounded-sm bg-[#252530] border border-white/[0.08] accent-[#D4AF53] cursor-pointer" />
                            <span class="text-xs text-[#F0EAD6]/45">Recordarme</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs text-[#D4AF53] hover:text-[#EDD98A] transition-colors">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>

                    <button type="submit"
                        class="w-full py-3 rounded-md text-[#0E0E12] text-sm font-medium uppercase tracking-widest hover:opacity-85 hover:-translate-y-px active:translate-y-0 transition-all"
                        style="background:linear-gradient(135deg,#D4AF53 0%,#B8952F 100%)">
                        Iniciar sesión
                    </button>
                </form>

            </div>
        </div>
    </div>
</x-guest-layout>