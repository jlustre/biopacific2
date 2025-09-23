<section id="testimonials" class="relative py-20 bg-gradient-to-b from-white via-slate-50 to-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-16">
            <h2 class="text-4xl font-extrabold text-secondary mb-4">What Families Are Saying</h2>
            <p class="text-lg text-slate-600 max-w-2xl mx-auto">
                Genuine voices from residents and families who trust our compassionate care every day.
            </p>
        </div>

        <!-- Testimonials Grid -->
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
            <!-- Testimonial Card -->
            <div
                class="bg-white shadow-lg rounded-3xl p-8 flex flex-col justify-between hover:shadow-2xl transition-all duration-300">
                <div class="mb-6">
                    <!-- Stars -->
                    <div class="flex items-center gap-1 mb-4 text-yellow-400">
                        <template x-for="star in 5" :key="star">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 
                00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 
                00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 
                1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 
                2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 
                0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 
                1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </template>
                    </div>
                    <p class="text-slate-600 text-lg leading-relaxed italic">
                        “The care and attention given to my mother have been beyond what we ever expected.
                        She feels safe, loved, and truly at home here.”
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <img src="https://images.unsplash.com/photo-1502685104226-ee32379fefbe?w=80&h=80&fit=crop&crop=face"
                        alt="Testimonial Avatar"
                        class="w-14 h-14 rounded-full object-cover ring-2 ring-accent shadow-md">
                    <div>
                        <h4 class="font-semibold text-secondary">Elena M.</h4>
                        <span class="text-sm text-slate-500">Daughter of Resident</span>
                    </div>
                </div>
            </div>

            <!-- More Testimonial Cards (Duplicate with different data) -->
            <div class="bg-white shadow-lg rounded-3xl p-8 hover:shadow-2xl transition-all duration-300">
                <!-- Stars -->
                <div class="flex items-center gap-1 mb-4 text-yellow-400">
                    <template x-for="star in 5" :key="star">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 
                00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 
                00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 
                1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 
                2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 
                0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 
                1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </template>
                </div>
                <p class="text-slate-600 text-lg leading-relaxed italic mb-6">
                    “Thanks to the amazing therapy team, my father is walking again after his surgery. Their
                    dedication and encouragement made all the difference.”
                </p>
                <div class="flex items-center gap-4">
                    <img src="https://images.unsplash.com/photo-1599566150163-29194dcaad36?w=80&h=80&fit=crop&crop=face"
                        alt="Testimonial Avatar"
                        class="w-14 h-14 rounded-full object-cover ring-2 ring-primary shadow-md">
                    <div>
                        <h4 class="font-semibold text-secondary">Dr. Chen</h4>
                        <span class="text-sm text-slate-500">Son of Resident</span>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-lg rounded-3xl p-8 hover:shadow-2xl transition-all duration-300">
                <!-- Stars -->
                <div class="flex items-center gap-1 mb-4 text-yellow-400">
                    <template x-for="star in 5" :key="star">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 
                00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 
                00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 
                1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 
                2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 
                0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 
                1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </template>
                </div>
                <p class="text-slate-600 text-lg leading-relaxed italic mb-6">
                    “The activities keep Mom engaged and happy. She has made wonderful friends and truly
                    feels at home.”
                </p>
                <div class="flex items-center gap-4">
                    <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=80&h=80&fit=crop&crop=face"
                        alt="Testimonial Avatar"
                        class="w-14 h-14 rounded-full object-cover ring-2 ring-green-500 shadow-md">
                    <div>
                        <h4 class="font-semibold text-secondary">Sarah P.</h4>
                        <span class="text-sm text-slate-500">Family Member</span>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-lg rounded-3xl p-8 hover:shadow-2xl transition-all duration-300">
                <!-- Stars -->
                <div class="flex items-center gap-1 mb-4 text-yellow-400">
                    <template x-for="star in 5" :key="star">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 
                00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 
                00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 
                1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 
                2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 
                0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 
                1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </template>
                </div>
                <p class="text-slate-600 text-lg leading-relaxed italic mb-6">
                    “The staff here are like family. Their compassion and professionalism give us peace of
                    mind knowing our loved one is in the best hands.”
                </p>
                <div class="flex items-center gap-4">
                    <img src="https://images.unsplash.com/photo-1527980965255-d3b416303d12?w=80&h=80&fit=crop&crop=face"
                        alt="Testimonial Avatar"
                        class="w-14 h-14 rounded-full object-cover ring-2 ring-blue-500 shadow-md">
                    <div>
                        <h4 class="font-semibold text-secondary">Michael R.</h4>
                        <span class="text-sm text-slate-500">Husband of Resident</span>
                    </div>
                </div>
            </div>

        </div>
        <!-- Call to Action -->
        <div class="mt-16 text-center">

            <p class="text-lg text-slate-700 mb-6">Want to hear more stories from our community?</p>
            <a href="#contact"
                class="inline-block bg-primary text-white px-8 py-3 rounded-full font-semibold shadow-lg hover:bg-primary/90 transition">
                Contact Us Today
            </a>
        </div>
</section>