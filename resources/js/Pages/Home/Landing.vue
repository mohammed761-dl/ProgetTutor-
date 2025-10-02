<script setup>
import { ref, computed } from "vue";
import { Link } from "@inertiajs/vue3";
import {
    Menu,
    Zap,
    Shield,
    Smartphone,
    Globe,
    Users,
    TrendingUp,
    Award,
    Clock,
    Github,
    Twitter,
    ArrowRight,
    Star,
    CheckCircle,
} from "lucide-vue-next";
import Button from "@/components/ui/button/Button.vue";
import Card from "@/components/ui/card/Card.vue";
import CardHeader from "@/components/ui/card/CardHeader.vue";
import CardTitle from "@/components/ui/card/CardTitle.vue";
import CardDescription from "@/components/ui/card/CardDescription.vue";
import CardContent from "@/components/ui/card/CardContent.vue";
import DropdownMenu from "@/components/ui/dropdown-menu/DropdownMenu.vue";
import DropdownMenuTrigger from "@/components/ui/dropdown-menu/DropdownMenuTrigger.vue";
import DropdownMenuContent from "@/components/ui/dropdown-menu/DropdownMenuContent.vue";
import DropdownMenuItem from "@/components/ui/dropdown-menu/DropdownMenuItem.vue";
import TooltipProvider from "@/components/ui/tooltip/TooltipProvider.vue";
import Tooltip from "@/components/ui/tooltip/Tooltip.vue";
import TooltipTrigger from "@/components/ui/tooltip/TooltipTrigger.vue";
import TooltipContent from "@/components/ui/tooltip/TooltipContent.vue";

const mobileMenuOpen = ref(false);
const currentYear = computed(() => new Date().getFullYear());

const features = [
    {
        title: "Lightning Fast",
        description:
            "Built with performance in mind. Your applications will load instantly with optimized code.",
        icon: Zap,
        gradient: "from-yellow-400 to-orange-500",
    },
    {
        title: "Secure by Default",
        description:
            "Enterprise-grade security features built-in to protect your data and users.",
        icon: Shield,
        gradient: "from-green-400 to-blue-500",
    },
    {
        title: "Mobile First",
        description:
            "Responsive design that works perfectly on all devices and screen sizes.",
        icon: Smartphone,
        gradient: "from-purple-400 to-pink-500",
    },
    {
        title: "Global Scale",
        description:
            "Deploy globally with CDN network for optimal performance worldwide.",
        icon: Globe,
        gradient: "from-blue-400 to-cyan-500",
    },
];

const stats = [
    { label: "Active Users", value: "10K+", icon: Users, change: "+12%" },
    { label: "Uptime", value: "99.9%", icon: TrendingUp, change: "+0.1%" },
    { label: "Awards", value: "12", icon: Award, change: "+3" },
    { label: "Response Time", value: "<100ms", icon: Clock, change: "-20ms" },
];

const testimonials = [
    {
        name: "Sarah Johnson",
        role: "CTO at TechCorp",
        content:
            "This platform transformed how we build applications. The speed and reliability are unmatched.",
        rating: 5,
    },
    {
        name: "Michael Chen",
        role: "Lead Developer",
        content:
            "The best development experience I've had. Everything just works seamlessly.",
        rating: 5,
    },
];
</script>

<template>
    <TooltipProvider>
        <div
            class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100 dark:from-slate-950 dark:via-slate-900 dark:to-slate-800"
        >
            <!-- Navigation -->
            <header
                class="sticky top-0 z-50 w-full border-b border-border/40 bg-background/80 backdrop-blur-xl supports-[backdrop-filter]:bg-background/60"
            >
                <div class="container flex h-16 max-w-screen-2xl items-center">
                    <div class="mr-4 hidden md:flex">
                        <Link
                            href="/dashboard"
                            class="mr-8 flex items-center space-x-2"
                        >
                            <div
                                class="h-8 w-8 rounded-lg bg-gradient-to-br from-blue-600 to-purple-600 flex items-center justify-center"
                            >
                                <span class="text-white font-bold text-sm"
                                    >YP</span
                                >
                            </div>
                            <span
                                class="hidden font-bold text-xl sm:inline-block bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent"
                            >
                                YourProject
                            </span>
                        </Link>
                        <nav
                            class="flex items-center gap-8 text-sm font-medium"
                        >
                            <a
                                class="transition-colors hover:text-foreground/80 text-foreground/60 hover:text-blue-600"
                                href="#features"
                                >Features</a
                            >
                            <a
                                class="transition-colors hover:text-foreground/80 text-foreground/60 hover:text-blue-600"
                                href="#about"
                                >About</a
                            >
                            <a
                                class="transition-colors hover:text-foreground/80 text-foreground/60 hover:text-blue-600"
                                href="#contact"
                                >Contact</a
                            >
                        </nav>
                    </div>
                    <div class="md:hidden flex items-center">
                        <DropdownMenu v-model:open="mobileMenuOpen">
                            <DropdownMenuTrigger as-child>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    aria-label="Open menu"
                                >
                                    <Menu class="h-5 w-5" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent
                                align="start"
                                class="w-48 mt-2"
                            >
                                <DropdownMenuItem as="a" href="#features"
                                    >Features</DropdownMenuItem
                                >
                                <DropdownMenuItem as="a" href="#about"
                                    >About</DropdownMenuItem
                                >
                                <DropdownMenuItem as="a" href="#contact"
                                    >Contact</DropdownMenuItem
                                >
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                    <div
                        class="flex flex-1 items-center justify-between space-x-2 md:justify-end"
                    >
                        <nav class="flex items-center gap-4">
                            <Link href="/login" as="button">
                                <Button variant="ghost">Sign In</Button>
                            </Link>
                            <Link href="/dashboard" as="button">
                                <Button
                                    class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700"
                                    >Get Started</Button
                                >
                            </Link>
                        </nav>
                    </div>
                </div>
            </header>

            <!-- Hero Section -->
            <section class="container relative overflow-hidden">
                <div
                    class="absolute inset-0 bg-gradient-to-r from-blue-600/10 via-purple-600/10 to-pink-600/10 blur-3xl"
                ></div>
                <div
                    class="relative mx-auto max-w-[980px] flex flex-col items-center gap-6 py-16 lg:py-32"
                >
                    <div
                        class="flex items-center gap-2 px-4 py-2 rounded-full bg-gradient-to-r from-blue-100 to-purple-100 dark:from-blue-900/30 dark:to-purple-900/30 border border-blue-200 dark:border-blue-800"
                    >
                        <Star class="h-4 w-4 text-blue-600" />
                        <span
                            class="text-sm font-medium text-blue-700 dark:text-blue-300"
                            >Trusted by 10,000+ developers</span
                        >
                    </div>
                    <h1
                        class="text-4xl md:text-7xl font-bold text-center leading-tight tracking-tighter"
                    >
                        Build amazing applications with
                        <span
                            class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent"
                        >
                            Laravel & Vue
                        </span>
                    </h1>
                    <p
                        class="text-xl sm:text-2xl text-muted-foreground text-center max-w-[750px] leading-relaxed"
                    >
                        Create powerful web applications with Laravel and
                        Vue.js. Fast, secure, and scalable solutions for your
                        business needs.
                    </p>
                    <div
                        class="flex flex-col sm:flex-row items-center justify-center gap-4 py-6"
                    >
                        <Link href="/dashboard" as="button">
                            <Button
                                size="lg"
                                class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-lg px-8 py-6 flex items-center gap-2"
                            >
                                Get Started Free
                                <ArrowRight class="h-5 w-5" />
                            </Button>
                        </Link>
                        <Button
                            variant="outline"
                            size="lg"
                            class="text-lg px-8 py-6 border-2 hover:bg-slate-50 dark:hover:bg-slate-800"
                        >
                            Watch Demo
                        </Button>
                    </div>
                    <div
                        class="flex items-center gap-6 text-sm text-muted-foreground"
                    >
                        <div class="flex items-center gap-2">
                            <CheckCircle class="h-4 w-4 text-green-500" />
                            <span>Free 14-day trial</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <CheckCircle class="h-4 w-4 text-green-500" />
                            <span>No credit card required</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section id="features" class="container space-y-12 py-16 lg:py-32">
                <div class="text-center max-w-[58rem] mx-auto space-y-6">
                    <h2 class="text-4xl md:text-6xl font-bold">
                        Powerful Features
                    </h2>
                    <p class="text-muted-foreground text-xl leading-relaxed">
                        Everything you need to build modern web applications
                        that scale
                    </p>
                </div>
                <div
                    class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4 max-w-7xl mx-auto"
                >
                    <Card
                        v-for="feature in features"
                        :key="feature.title"
                        class="group hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border-0 bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm"
                    >
                        <CardHeader
                            class="flex flex-col items-center justify-center gap-4 p-8 text-center"
                        >
                            <div
                                :class="`h-16 w-16 rounded-2xl bg-gradient-to-br ${feature.gradient} flex items-center justify-center group-hover:scale-110 transition-transform duration-300`"
                            >
                                <component
                                    :is="feature.icon"
                                    class="h-8 w-8 text-white"
                                />
                            </div>
                            <CardTitle class="text-xl">{{
                                feature.title
                            }}</CardTitle>
                            <CardDescription
                                class="text-base leading-relaxed"
                                >{{ feature.description }}</CardDescription
                            >
                        </CardHeader>
                    </Card>
                </div>
            </section>

            <!-- Stats Section -->
            <section class="container py-16 lg:py-24">
                <div
                    class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4 max-w-5xl mx-auto"
                >
                    <Card
                        v-for="stat in stats"
                        :key="stat.label"
                        class="text-center p-8 hover:shadow-lg transition-shadow duration-300 bg-gradient-to-br from-white to-slate-50 dark:from-slate-800 dark:to-slate-900"
                    >
                        <CardContent class="space-y-4">
                            <component
                                :is="stat.icon"
                                class="h-10 w-10 text-blue-600 mx-auto"
                            />
                            <div class="space-y-2">
                                <div
                                    class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent"
                                >
                                    {{ stat.value }}
                                </div>
                                <CardDescription
                                    class="text-base font-medium"
                                    >{{ stat.label }}</CardDescription
                                >
                                <div class="text-sm text-green-600 font-medium">
                                    {{ stat.change }}
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </section>

            <!-- Testimonials -->
            <section class="container py-16 lg:py-24">
                <div class="text-center max-w-[58rem] mx-auto space-y-6 mb-12">
                    <h2 class="text-4xl md:text-5xl font-bold">
                        What developers say
                    </h2>
                    <p class="text-muted-foreground text-xl">
                        Join thousands of satisfied developers
                    </p>
                </div>
                <div class="grid gap-8 md:grid-cols-2 max-w-4xl mx-auto">
                    <Card
                        v-for="testimonial in testimonials"
                        :key="testimonial.name"
                        class="p-8 bg-gradient-to-br from-white to-slate-50 dark:from-slate-800 dark:to-slate-900"
                    >
                        <CardContent class="space-y-4">
                            <div class="flex gap-1">
                                <Star
                                    v-for="i in testimonial.rating"
                                    :key="i"
                                    class="h-5 w-5 fill-yellow-400 text-yellow-400"
                                />
                            </div>
                            <p class="text-lg leading-relaxed">
                                "{{ testimonial.content }}"
                            </p>
                            <div>
                                <div class="font-semibold">
                                    {{ testimonial.name }}
                                </div>
                                <div class="text-sm text-muted-foreground">
                                    {{ testimonial.role }}
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </section>

            <!-- CTA Section -->
            <section class="container py-16 lg:py-32">
                <div
                    class="max-w-[58rem] mx-auto text-center space-y-8 p-12 rounded-3xl bg-gradient-to-br from-blue-600 to-purple-600 text-white"
                >
                    <h2 class="text-4xl md:text-6xl font-bold">
                        Ready to get started?
                    </h2>
                    <p class="text-xl opacity-90 leading-relaxed">
                        Join thousands of developers building amazing
                        applications with our platform.
                    </p>
                    <div
                        class="flex flex-col sm:flex-row justify-center gap-4 pt-4"
                    >
                        <Link href="/signup" as="button">
                            <Button
                                size="lg"
                                variant="secondary"
                                class="text-lg px-8 py-6"
                                >Start Building Today</Button
                            >
                        </Link>
                        <Button
                            size="lg"
                            variant="outline"
                            class="text-lg px-8 py-6 border-white/20 text-white hover:bg-white/10"
                        >
                            Talk to Sales
                        </Button>
                    </div>
                </div>
            </section>

            <!-- Footer -->
            <footer
                class="border-t border-border bg-slate-50 dark:bg-slate-900"
            >
                <div
                    class="container flex flex-col md:flex-row items-center justify-between py-12 gap-6"
                >
                    <div class="flex flex-col md:flex-row items-center gap-4">
                        <div
                            class="h-8 w-8 rounded-lg bg-gradient-to-br from-blue-600 to-purple-600 flex items-center justify-center"
                        >
                            <span class="text-white font-bold text-sm">YP</span>
                        </div>
                        <p
                            class="text-sm text-muted-foreground text-center md:text-left"
                        >
                            Built with Laravel and Vue.js. ©
                            {{ currentYear }} YourProject. All rights reserved.
                        </p>
                    </div>
                    <div class="flex items-center gap-6">
                        <Tooltip>
                            <TooltipTrigger as-child>
                                <a
                                    href="#"
                                    class="text-muted-foreground hover:text-blue-600 transition-colors"
                                    aria-label="GitHub"
                                >
                                    <Github class="h-6 w-6" />
                                </a>
                            </TooltipTrigger>
                            <TooltipContent>GitHub</TooltipContent>
                        </Tooltip>
                        <Tooltip>
                            <TooltipTrigger as-child>
                                <a
                                    href="#"
                                    class="text-muted-foreground hover:text-blue-600 transition-colors"
                                    aria-label="Twitter"
                                >
                                    <Twitter class="h-6 w-6" />
                                </a>
                            </TooltipTrigger>
                            <TooltipContent>Twitter</TooltipContent>
                        </Tooltip>
                    </div>
                </div>
            </footer>
        </div>
    </TooltipProvider>
</template>
