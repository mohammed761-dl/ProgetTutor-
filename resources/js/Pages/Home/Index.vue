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
    FileText,
    Calculator,
    Truck,
    Building,
    BarChart3,
    Settings,
    Headphones,
    Mail,
    Phone,
    MapPin,
} from "lucide-vue-next";
import Button from "@/components/ui/button/Button.vue";
import Card from "@/components/ui/card/Card.vue";
import CardHeader from "@/components/ui/card/CardHeader.vue";
import CardTitle from "@/components/ui/card/CardTitle.vue";
import CardDescription from "@/components/ui/card/CardDescription.vue";
import CardContent from "@/components/ui/card/CardContent.vue";
import Separator from "@/components/ui/separator/Separator.vue";
import DropdownMenu from "@/components/ui/dropdown-menu/DropdownMenu.vue";
import DropdownMenuTrigger from "@/components/ui/dropdown-menu/DropdownMenuTrigger.vue";
import DropdownMenuContent from "@/components/ui/dropdown-menu/DropdownMenuContent.vue";
import DropdownMenuItem from "@/components/ui/dropdown-menu/DropdownMenuItem.vue";
import Tooltip from "@/components/ui/tooltip/Tooltip.vue";
import TooltipTrigger from "@/components/ui/tooltip/TooltipTrigger.vue";
import TooltipContent from "@/components/ui/tooltip/TooltipContent.vue";
import TooltipProvider from "@/components/ui/tooltip/TooltipProvider.vue";
import Input from "@/components/ui/input/Input.vue";

const mobileMenuOpen = ref(false);
const currentYear = computed(() => new Date().getFullYear());

const features = [
    {
        title: "Invoice Management",
        description:
            "Create, manage, and track invoices with automated billing and payment reminders.",
        icon: FileText,

        route: "/invoices",
    },
    {
        title: "Quote Generation",
        description:
            "Generate professional quotes quickly with customizable templates and pricing.",
        icon: Calculator,

        route: "/devis",
    },
    {
        title: "Purchase Orders",
        description:
            "Streamline procurement with automated purchase order creation and tracking.",
        icon: Building,

        route: "/po",
    },
    {
        title: "Delivery Notes",
        description:
            "Track shipments and deliveries with detailed delivery note management.",
        icon: Truck,

        route: "/note",
    },
    {
        title: "Authorization Requests",
        description:
            "Manage authorization request orders with comprehensive tracking and approval workflows.",
        icon: Shield,

        route: "/aro",
    },
    {
        title: "Document Management",
        description:
            "Centralized document storage and management for all your business documents.",
        icon: BarChart3,

        route: "/document",
    },
];

const stats = [
    { label: "Active Users", value: "5,000+", icon: Users, change: "+15%" },
    {
        label: "Invoices Generated",
        value: "50K+",
        icon: FileText,
        change: "+25%",
    },
    { label: "Customer Satisfaction", value: "98%", icon: Star, change: "+2%" },
    { label: "Uptime", value: "99.9%", icon: TrendingUp, change: "+0.1%" },
];

const testimonials = [
    {
        name: "Sarah Johnson",
        role: "CEO at TechCorp",
        content:
            "Senselix ERP transformed our business operations. We've seen a 40% increase in efficiency and better customer relationships.",
        rating: 5,
        company: "TechCorp",
    },
    {
        name: "Michael Chen",
        role: "Operations Manager",
        content:
            "The invoice and quote management features are incredible. We've reduced processing time by 60% and improved accuracy.",
        rating: 5,
        company: "Global Solutions",
    },
    {
        name: "Emily Rodriguez",
        role: "Finance Director",
        content:
            "The document management and authorization workflows have streamlined our approval processes significantly.",
        rating: 5,
        company: "Innovate Inc",
    },
];

const pricingPlans = [
    {
        name: "Starter",
        price: "$29",
        period: "per month",
        description: "Perfect for small businesses getting started",
        features: [
            "Up to 5 users",
            "Basic invoice management",
            "Quote generation",
            "Email support",
            "5GB storage",
        ],
        popular: false,
    },
    {
        name: "Professional",
        price: "$79",
        period: "per month",
        description: "Ideal for growing businesses",
        features: [
            "Up to 25 users",
            "Advanced invoice management",
            "Purchase order tracking",
            "Delivery notes",
            "Priority support",
            "25GB storage",
            "Analytics dashboard",
        ],
        popular: true,
    },
    {
        name: "Enterprise",
        price: "$199",
        period: "per month",
        description: "For large organizations with complex needs",
        features: [
            "Unlimited users",
            "Custom integrations",
            "Advanced analytics",
            "Dedicated support",
            "Unlimited storage",
            "API access",
            "White-label options",
        ],
        popular: false,
    },
];

// Contact form state
const contact = ref({ name: "", email: "", company: "", message: "" });
const contactSuccess = ref(false);
const contactLoading = ref(false);

const handleContactSubmit = async () => {
    contactLoading.value = true;
    setTimeout(() => {
        contactSuccess.value = true;
        contactLoading.value = false;
        contact.value = { name: "", email: "", company: "", message: "" };
    }, 1200);
};
</script>

<template>
    <TooltipProvider>
        <div class="min-h-screen bg-background">
            <!-- ================= NAVIGATION ================= -->
            <header
                class="sticky top-0 z-50 w-full border-b border-border/40 bg-background/80 backdrop-blur-xl supports-[backdrop-filter]:bg-background/60"
            >
                <div class="container flex h-16 max-w-screen-2xl items-center px-6 lg:px-8">
                    <div class="mr-8 hidden md:flex">
                        <Link href="/" class="flex items-center space-x-3">
                            <div class="flex items-center justify-center w-8 h-8 bg-primary rounded-lg">
                                <span class="text-white font-bold text-lg">S</span>
                            </div>
                            <span
                                class="font-bold text-xl text-foreground"
                            >
                                Senselix ERP
                            </span>
                        </Link>
                    </div>
                    
                    <!-- Mobile Menu Toggle -->
                    <div class="md:hidden flex items-center mr-4">
                        <Link href="/" class="flex items-center space-x-2 mr-4">
                            <div class="flex items-center justify-center w-7 h-7 bg-primary rounded-lg">
                                <span class="text-white font-bold text-sm">S</span>
                            </div>
                            <span class="font-bold text-lg text-foreground">
                                Senselix
                            </span>
                        </Link>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="hidden md:flex items-center space-x-4 ml-auto">
                        <Link href="/login" as="button">
                            <Button class="text-sm font-medium px-6">
                                Sign In
                            </Button>
                        </Link>
                    </div>
                    
                    <!-- Mobile Sign Up Button -->
                    <div class="md:hidden ml-auto">
                        <Link href="/login" as="button">
                            <Button size="sm">Sign Up</Button>
                        </Link>
                    </div>
                </div>
            </header>

            <!-- ================= HERO SECTION ================= -->
            <section class="container relative overflow-hidden">
                <div class="absolute inset-0 bg-muted/50"></div>
                <div
                    class="relative mx-auto max-w-[980px] flex flex-col items-center gap-6 py-16 lg:py-32"
                >
                    <div
                        class="flex items-center gap-2 px-4 py-2 rounded-full bg-muted border border-border"
                    >
                        <Star class="h-4 w-4 text-muted-foreground" />
                        <span class="text-sm font-medium text-muted-foreground"
                            >Trusted by 5,000+ businesses</span
                        >
                    </div>
                    <h1
                        class="text-4xl md:text-7xl font-bold text-center leading-tight tracking-tighter"
                    >
                        Complete ERP Solution for
                        <span class="text-primary"> Modern Businesses </span>
                    </h1>
                    <p
                        class="text-xl sm:text-2xl text-muted-foreground text-center max-w-[750px] leading-relaxed"
                    >
                        Streamline your business operations with our
                        comprehensive ERP system. Manage invoices, quotes,
                        purchase orders, and more with ease.
                    </p>
                    <div
                        class="flex flex-col sm:flex-row items-center justify-center gap-4 py-6"
                    >
                        <Link href="/login" as="button">
                            <Button
                                size="lg"
                                class="text-lg px-8 py-6 flex items-center gap-2"
                            >
                                Get Started
                                <ArrowRight class="h-5 w-5" />
                            </Button>
                        </Link>
                        <Button
                            variant="outline"
                            size="lg"
                            class="text-lg px-8 py-6"
                        >
                            Watch Demo
                        </Button>
                    </div>
                    <div
                        class="flex items-center gap-6 text-sm text-muted-foreground"
                    >
                        <div class="flex items-center gap-2">
                            <CheckCircle class="h-4 w-4 text-primary" />
                            <span>Enterprise-grade security</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <CheckCircle class="h-4 w-4 text-primary" />
                            <span>24/7 support</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <CheckCircle class="h-4 w-4 text-primary" />
                            <span>99.9% uptime</span>
                        </div>
                    </div>
                </div>
            </section>

            

         
            <!-- ================= FOOTER ================= -->
            <footer
                class="border-t border-border bg-slate-50 dark:bg-slate-900"
            >
                <div class="container py-12">
                       
                            
                    <div
                        class="flex flex-col md:flex-row items-center justify-between gap-4"
                    >
                        <p class="text-sm text-muted-foreground">
                            © {{ currentYear }} Senselix ERP. All rights
                            reserved.
                        </p>
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
                </div>
            </footer>
        </div>
    </TooltipProvider>
</template>
