<x-guest-layout>
    <div class="min-h-screen bg-[#0E0E12] flex">

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
                    "Un pequeño paso de verificación, un gran salto hacia adelante."
                </blockquote>
                <cite class="text-xs uppercase tracking-widest text-[#F0EAD6]/40 not-italic">— Anónimo</cite>
            </div>
        </div>

        <div class="flex flex-col justify-center items-center w-full lg:w-[480px] px-8 py-12 bg-[#16161C] border-l border-white/[0.06]">
            <div class="w-full max-w-sm">

                {{-- Ícono de sobre --}}
                <div class="w-14 h-14 rounded-full flex items-center justify-center mb-6 border border-[#D4AF53]/25 bg-[#D4AF53]/10">
                    <svg class="w-6 h-6 text-[#D4AF53]" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                </div>

                <h1 class="font-serif text-[2rem] font-normal text-[#F0EAD6] leading-tight mb-2">Verifica tu correo</h1>
                <div class="w-10 h-0.5 mb-6" style="background:linear-gradient(90deg,#D4AF53,transparent)"></div>
                <p class="text-sm text-[#F0EAD6]/45 mb-8 leading-relaxed">
                    Gracias por registrarte. Hemos enviado un enlace de verificación a tu correo. Revisa tu bandeja de entrada y haz clic en el enlace para activar tu cuenta.
                </p>

                @if (session('status') == 'verification-link-sent')
                    <div class="mb-6 text-sm text-[#6DBFA0] bg-[#6DBFA0]/10 border border-[#6DBFA0]/25 rounded-md px-4 py-3">
                        Se ha enviado un nuevo enlace de verificación a tu correo.
                    </div>
                @endif

                <div class="flex items-center gap-4">
                    <form method="POST" action="{{ route('verification.send') }}" class="flex-1">
                        @csrf
                        <button type="submit"
                            class="w-full py-3 rounded-md text-[#0E0E12] text-sm font-medium uppercase tracking-widest hover:opacity-85 hover:-translate-y-px active:translate-y-0 transition-all"
                            style="background:linear-gradient(135deg,#D4AF53 0%,#B8952F 100%)">
                            Reenviar correo
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-xs text-[#F0EAD6]/40 hover:text-[#F0EAD6] transition-colors whitespace-nowrap bg-transparent border-none cursor-pointer">
                            Cerrar sesión
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</x-guest-layout>