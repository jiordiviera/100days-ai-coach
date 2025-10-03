<div
    x-data="{ showHero: false }"
    x-init="() => requestAnimationFrame(() => { showHero = true; })"
>
    <!-- Hero Section -->
    <section class="relative bg-background overflow-hidden">
        <!-- Hero Background Pattern -->
        <div
            class="absolute inset-0 bg-grid-slate-100 dark:bg-grid-slate-900 [mask-image:linear-gradient(0deg,white,rgba(255,255,255,0.6))] dark:[mask-image:linear-gradient(0deg,black,rgba(0,0,0,0.6))]"
        ></div>

        <div
            class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-24 lg:py-32"
        >
            <div class="lg:grid lg:grid-cols-12 lg:gap-8">
                <div
                    class="sm:text-center md:max-w-2xl md:mx-auto lg:col-span-6 lg:text-left"
                >
                    <h1
                        x-show="showHero"
                        x-transition:enter="transition ease-out duration-700"
                        x-transition:enter-start="opacity-0 translate-y-6"
                        x-transition:enter-end="opacity-100 translate-y-0"
                    >
            <span
                class="block text-base font-semibold text-primary tracking-wide uppercase"
            >
              Défi de codage
            </span>
                        <span
                            class="mt-1 block text-4xl tracking-tight font-extrabold sm:text-5xl xl:text-6xl"
                        >
              <span class="block text-foreground">Relevez le défi des</span>
              <span
                  class="block bg-clip-text text-transparent bg-gradient-to-r from-primary to-secondary/80 pb-1"
              >
                100 Days of Code
              </span>
            </span>
                    </h1>
                    <p
                        x-show="showHero"
                        x-transition:enter="transition ease-out duration-700 delay-200"
                        x-transition:enter-start="opacity-0 translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="mt-3 text-base text-foreground/80 sm:mt-5 sm:text-xl lg:text-lg xl:text-xl"
                    >
                        Transformez votre apprentissage en vous engageant à coder au moins
                        une heure par jour pendant 100 jours consécutifs. Suivez votre
                        progression, gérez vos projets et atteignez vos objectifs!
                    </p>
                    <div class="mt-8 sm:flex sm:justify-center lg:justify-start">
                        <div class="rounded-md shadow">
                            <a
                                href="{{ route("register") }}"
                                class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-foreground bg-primary md:py-4 md:text-lg md:px-10"
                            >
                                @auth
                                    Suivre le challenge
                                @else
                                    Commencer le défi
                                @endauth
                            </a>
                        </div>
                        <div class="mt-3 sm:mt-0 sm:ml-3">
                            <a
                                href="#about"
                                class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-accent-foreground bg-muted-foreground/30 md:py-4 md:text-lg md:px-10"
                            >
                                En savoir plus
                            </a>
                        </div>
                    </div>
                </div>
                <div
                    class="mt-12 relative sm:max-w-lg sm:mx-auto lg:mt-0 lg:max-w-none lg:mx-0 lg:col-span-6 lg:flex lg:items-center"
                >
                    <div
                        class="relative mx-auto w-full rounded-lg shadow-lg lg:max-w-md transition-all duration-700 ease-out"
                        x-bind:class="showHero ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
                    >
                        <div
                            class="relative block w-full rounded-lg overflow-hidden"
                        >
                            <img
                                class="w-full"
                                src="https://images.unsplash.com/photo-1517694712202-14dd9538aa97?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80"
                                alt="Coding"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section
        id="about"
        class="py-16 bg-muted-foreground/20 overflow-hidden"
        x-data="{ visible: false }"
        x-init="() => {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        visible = true;
                        observer.disconnect();
                    }
                });
            }, { threshold: 0.2 });
            observer.observe($el);
        }"
    >
        <div
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 transition-all duration-700 ease-out"
            x-bind:class="visible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
        >
            <div class="lg:text-center">
                <h2
                    class="text-base text-primary font-semibold tracking-wide uppercase"
                >
                    Fonctionnalités
                </h2>
                <p
                    class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-foreground sm:text-4xl"
                >
                    Une meilleure façon de suivre votre défi
                </p>
                <p class="mt-4 max-w-2xl text-xl text-foreground/80 lg:mx-auto">
                    Notre plateforme vous aide à rester motivé et organisé tout au long de
                    votre parcours de 100 jours.
                </p>
            </div>

            <div class="mt-10">
                <dl
                    class="space-y-10 md:space-y-0 md:grid md:grid-cols-2 md:gap-x-8 md:gap-y-10"
                >
                    <div
                        class="relative transition-all duration-700 ease-out"
                        x-bind:class="visible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
                        x-bind:style="visible ? 'transition-delay: 120ms' : ''"
                    >
                        <dt>
                            <div
                                class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-primary text-foreground"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
                                    />
                                </svg>
                            </div>
                            <p class="ml-16 text-lg leading-6 font-medium text-foreground">
                                Suivi de projets
                            </p>
                        </dt>
                        <dd class="mt-2 ml-16 text-base text-foreground/80">
                            Créez et organisez vos projets de code. Définissez des objectifs
                            clairs pour chaque projet et suivez votre progression.
                        </dd>
                    </div>

                    <div
                        class="relative transition-all duration-700 ease-out"
                        x-bind:class="visible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
                        x-bind:style="visible ? 'transition-delay: 180ms' : ''"
                    >
                        <dt>
                            <div
                                class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-primary text-foreground"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>
                            <p class="ml-16 text-lg leading-6 font-medium text-foreground">
                                Gestion de tâches
                            </p>
                        </dt>
                        <dd class="mt-2 ml-16 text-base text-foreground/80">
                            Décomposez vos projets en tâches gérables. Planifiez votre travail
                            quotidien et restez concentré sur vos objectifs.
                        </dd>
                    </div>

                    <div
                        class="relative transition-all duration-700 ease-out"
                        x-bind:class="visible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
                        x-bind:style="visible ? 'transition-delay: 240ms' : ''"
                    >
                        <dt>
                            <div
                                class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-primary text-foreground"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                    />
                                </svg>
                            </div>
                            <p class="ml-16 text-lg leading-6 font-medium text-foreground">
                                Collaboration
                            </p>
                        </dt>
                        <dd class="mt-2 ml-16 text-base text-foreground/80">
                            Invitez des amis ou des collègues à rejoindre vos projets.
                            Collaborez efficacement et apprenez ensemble.
                        </dd>
                    </div>

                    <div
                        class="relative transition-all duration-700 ease-out"
                        x-bind:class="visible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
                        x-bind:style="visible ? 'transition-delay: 300ms' : ''"
                    >
                        <dt>
                            <div
                                class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-primary text-foreground"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                    />
                                </svg>
                            </div>
                            <p class="ml-16 text-lg leading-6 font-medium text-foreground">
                                Suivi de progression
                            </p>
                        </dt>
                        <dd class="mt-2 ml-16 text-base text-foreground/80">
                            Visualisez votre progression au fil des jours. Célébrez vos
                            réussites et restez motivé jusqu'à la fin du défi.
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section
        class="relative py-16 bg-primary"
        x-data="{ visible: false }"
        x-init="() => {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        visible = true;
                        observer.disconnect();
                    }
                });
            }, { threshold: 0.2 });
            observer.observe($el);
        }"
    >
        <div
            class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col items-center transition-all duration-700 ease-out"
            x-bind:class="visible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
        >
            <h2 class="text-3xl font-extrabold tracking-tight  sm:text-4xl text-center">
                <span class="block">Prêt à relever le défi?</span>
                <span class="block">Commencez dès aujourd'hui.</span>
            </h2>
            <p class="mt-4 text-lg leading-6 max-w-2xl text-center font-medium">
                Rejoignez des milliers de développeurs qui transforment leur vie en
                codant chaque jour. Inscrivez-vous gratuitement et démarrez votre
                parcours des 100 jours de code.
            </p>
            <div class="mt-8 flex justify-center">
                <div class="inline-flex rounded-md shadow">
                    <a
                        href="{{ route("register") }}"
                        class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-primary bg-foreground"
                    >
                        Créer un compte
                    </a>
                </div>
                <div class="ml-3 inline-flex">
                    <a
                        href="{{ route("login") }}"
                        class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-foreground bg-secondary bg-opacity-60 hover:bg-opacity-70"
                    >
                        Se connecter
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section
        class="py-16 overflow-hidden"
        x-data="{ visible: false }"
        x-init="() => {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        visible = true;
                        observer.disconnect();
                    }
                });
            }, { threshold: 0.2 });
            observer.observe($el);
        }"
    >
        <div
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 transition-all duration-700 ease-out"
            x-bind:class="visible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
        >
            <div class="lg:text-center">
                <h2
                    class="text-base text-primary font-semibold tracking-wide uppercase"
                >
                    Témoignages
                </h2>
                <p
                    class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-foreground sm:text-4xl"
                >
                    Ce que nos utilisateurs disent
                </p>
            </div>
            <div class="mt-10 grid gap-8 lg:grid-cols-3">
                <div
                    class="bg-muted-foreground/20 rounded-lg shadow-md p-6 transition-all duration-700 ease-out"
                    x-bind:class="visible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
                >
                    <div class="flex items-center mb-4">
                        <svg
                            class="h-5 w-5 text-yellow-400"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                            />
                        </svg>
                        <svg
                            class="h-5 w-5 text-yellow-400"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                            />
                        </svg>
                        <svg
                            class="h-5 w-5 text-yellow-400"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                            />
                        </svg>
                        <svg
                            class="h-5 w-5 text-yellow-400"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                            />
                        </svg>
                        <svg
                            class="h-5 w-5 text-yellow-400"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                            />
                        </svg>
                    </div>
                    <p class="italic mb-4">
                        "Cette application a complètement transformé ma façon d'aborder le
                        défi des 100 jours de code. Grâce à elle, je reste organisé et
                        motivé chaque jour."
                    </p>
                    <div class="flex items-center">
                        <div
                            class="h-10 w-10 rounded-full bg-primary/80 flex items-center justify-center  font-bold"
                        >
                            SL
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-foreground">
                                Sophie Lambert
                            </p>
                            <p class="text-sm ">
                                Développeuse Web
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-muted-foreground/20 rounded-lg shadow-md p-6 transition-all duration-700 ease-out"
                    x-bind:class="visible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
                    x-bind:style="visible ? 'transition-delay: 120ms' : ''"
                >
                    <div class="flex items-center mb-4">
                        <svg
                            class="h-5 w-5 text-yellow-400"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                            />
                        </svg>
                        <svg
                            class="h-5 w-5 text-yellow-400"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                            />
                        </svg>
                        <svg
                            class="h-5 w-5 text-yellow-400"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                            />
                        </svg>
                        <svg
                            class="h-5 w-5 text-yellow-400"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                            />
                        </svg>
                        <svg
                            class="h-5 w-5 text-yellow-400"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                            />
                        </svg>
                    </div>
                    <p class="italic mb-4">
                        "J'ai essayé plusieurs fois le défi des 100 jours sans succès. Avec
                        cette plateforme, j'ai enfin réussi à atteindre mon objectif. La
                        gestion de projets est incroyable!"
                    </p>
                    <div class="flex items-center">
                        <div
                            class="h-10 w-10 rounded-full bg-primary/80 flex items-center justify-center font-bold"
                        >
                            TD
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-foreground">
                                Thomas Dubois
                            </p>
                            <p class="text-sm">
                                Étudiant en informatique
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-muted-foreground/20 rounded-lg shadow-md p-6 transition-all duration-700 ease-out"
                    x-bind:class="visible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
                    x-bind:style="visible ? 'transition-delay: 180ms' : ''"
                >
                    <div class="flex items-center mb-4">
                        <svg
                            class="h-5 w-5 text-yellow-400"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                            />
                        </svg>
                        <svg
                            class="h-5 w-5 text-yellow-400"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                            />
                        </svg>
                        <svg
                            class="h-5 w-5 text-yellow-400"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                            />
                        </svg>
                        <svg
                            class="h-5 w-5 text-yellow-400"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                            />
                        </svg>
                        <svg
                            class="h-5 w-5 text-yellow-400"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                            />
                        </svg>
                    </div>
                    <p class="italic mb-4">
                        "La fonction de collaboration est géniale! Notre équipe utilise
                        cette plateforme pour coordonner nos projets et suivre notre
                        progression collective pendant le défi."
                    </p>
                    <div class="flex items-center">
                        <div
                            class="h-10 w-10 rounded-full bg-primary/80 flex items-center justify-center  font-bold"
                        >
                            ML
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-foreground">
                                Marie Leclerc
                            </p>
                            <p class="text-sm">
                                Lead Developer
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
