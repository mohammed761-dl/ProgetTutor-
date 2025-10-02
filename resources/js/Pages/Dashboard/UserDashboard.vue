<script setup>
// filepath: resources/js/Pages/Dashboard/UserDashboard.vue

// Layout
import Layout from "../../Layout/App.vue";
// ShadCN Card components
import Card from "@/components/ui/card/Card.vue";
import CardHeader from "@/components/ui/card/CardHeader.vue";
import CardTitle from "@/components/ui/card/CardTitle.vue";
import CardDescription from "@/components/ui/card/CardDescription.vue";
import CardContent from "@/components/ui/card/CardContent.vue";
import Badge from "@/components/ui/badge/Badge.vue";
// ShadCN Table components
import Table from "@/components/ui/table/Table.vue";
import TableHeader from "@/components/ui/table/TableHeader.vue";
import TableBody from "@/components/ui/table/TableBody.vue";
import TableRow from "@/components/ui/table/TableRow.vue";
import TableCell from "@/components/ui/table/TableCell.vue";
import TableHead from "@/components/ui/table/TableHead.vue";
import { ref, computed } from "vue";

// Charts
import { Line, Doughnut } from "vue-chartjs";
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale,
    ArcElement,
} from "chart.js";

ChartJS.register(
    Title,
    Tooltip,
    Legend,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale,
    ArcElement
);

// ✅ Props from UserDashboard controller
const props = defineProps({
    user: Object,
    stats: Object
});

// ✅ Real Line Chart Data from your database
const lineChartData = computed(() => ({
    labels: props.stats?.monthlyRevenue?.labels || ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    datasets: [
        {
            label: "Revenue (€)",
            data: props.stats?.monthlyRevenue?.revenue || [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            borderColor: "rgb(34, 197, 94)",
            backgroundColor: "rgba(34, 197, 94, 0.1)",
            fill: true,
            tension: 0.4,
        },
        {
            label: "Expenses (€)",
            data: props.stats?.monthlyRevenue?.expenses || [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            borderColor: "rgb(239, 68, 68)",
            backgroundColor: "rgba(239, 68, 68, 0.1)",
            fill: true,
            tension: 0.4,
        },
        {
            label: "Profit (€)",
            data: props.stats?.monthlyRevenue?.profit || [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            borderColor: "rgb(59, 130, 246)",
            backgroundColor: "rgba(59, 130, 246, 0.1)",
            fill: true,
            tension: 0.4,
        },
    ],
}));

const lineChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: "top" },
        title: { 
            display: true, 
            text: "Monthly Financial Overview - Last 12 Months",
            font: { size: 16, weight: 'bold' }
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: function(value) {
                    return '€' + value.toLocaleString();
                }
            }
        }
    }
};

// ✅ Real Doughnut Chart Data - Sales Documents
const doughnutChartData = computed(() => ({
    labels: ["Total Quotes", "Total Invoices", "Pending Quotes"],
    datasets: [
        {
            label: "Sales Documents",
            data: [
                props.stats?.totalQuotes || 0,
                props.stats?.totalInvoices || 0, 
                props.stats?.pendingQuotes || 0
            ],
            backgroundColor: [
                "rgb(99, 102, 241)",
                "rgb(34, 197, 94)",
                "rgb(245, 158, 11)",
            ],
            hoverOffset: 4,
        },
    ],
}));

// ✅ Real Doughnut Chart Data - Financial Analysis
const doughnutChartData2 = computed(() => ({
    labels: ["Total Revenue (€)", "Average Invoice (€)"],
    datasets: [
        {
            label: "Financial Analysis",
            data: [
                (props.stats?.totalRevenue || 0) / 1000, // Scale down for visualization
                (props.stats?.averageInvoiceAmount || 0)
            ],
            backgroundColor: [
                "rgb(168, 85, 247)", 
                "rgb(236, 72, 153)"
            ],
            hoverOffset: 4,
        },
    ],
}));

const doughnutChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: "top" },
        title: { 
            display: true, 
            text: "Sales Documents Overview",
            font: { size: 14, weight: 'bold' }
        },
    },
};

const doughnutChartOptions2 = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: "top" },
        title: { 
            display: true, 
            text: "Financial Analysis",
            font: { size: 14, weight: 'bold' }
        },
        tooltip: {
            callbacks: {
                label: function(context) {
                    if (context.label.includes('Total Revenue')) {
                        return context.label + ': €' + (context.parsed * 1000).toLocaleString();
                    }
                    return context.label + ': €' + context.parsed.toLocaleString();
                }
            }
        }
    },
};

// ✅ Recent quotes table functionality
const itemsPerPage = 5;
const currentPage = ref(1);

const recentQuotes = computed(() => {
    return props.stats?.recentQuotes || [];
});

const paginatedQuotes = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return recentQuotes.value.slice(start, end);
});

const totalPages = computed(() => {
    return Math.ceil(recentQuotes.value.length / itemsPerPage);
});

// ✅ Helper functions
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'EUR'
    }).format(amount || 0);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const getStatusBadgeVariant = (status) => {
    const variants = {
        'draft': 'secondary',
        'sent': 'default',
        'accepted': 'success',
        'rejected': 'destructive',
        'expired': 'warning'
    };
    return variants[status?.toLowerCase()] || 'default';
};

// ✅ Calculate metrics
const conversionRate = computed(() => {
    const total = props.stats?.totalQuotes || 0;
    const invoices = props.stats?.totalInvoices || 0;
    return total > 0 ? ((invoices / total) * 100).toFixed(1) : '0.0';
});

const roi = computed(() => {
    const revenue = props.stats?.totalRevenue || 0;
    const expenses = revenue * 0.7; // Estimate expenses as 70% of revenue
    const profit = revenue - expenses;
    return expenses > 0 ? ((profit / expenses) * 100).toFixed(1) : '0.0';
});
</script>

<template>
    <Layout>
        <!-- ✅ Real Statistics Cards Grid -->
        <div class="grid gap-6 md:grid-cols-3 mb-8">
            <!-- Total Quotes -->
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-base font-medium">Total Quotes</CardTitle>
                    <CardDescription>
                        Total number of quotes in the system
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-blue-600">
                        {{ stats?.totalQuotes || 0 }}
                    </div>
                </CardContent>
            </Card>

            <!-- Total Invoices -->
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-base font-medium">Total Invoices</CardTitle>
                    <CardDescription>
                        Total number of invoices generated
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-green-600">
                        {{ stats?.totalInvoices || 0 }}
                    </div>
                </CardContent>
            </Card>

            <!-- Pending Quotes -->
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-base font-medium">Pending Quotes</CardTitle>
                    <CardDescription>
                        Number of quotes pending or in progress
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-orange-600">
                        {{ stats?.pendingQuotes || 0 }}
                    </div>
                </CardContent>
            </Card>

            <!-- Conversion Rate -->
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-base font-medium">Conversion Rate (%)</CardTitle>
                    <CardDescription>
                        Quote to invoice conversion rate
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-purple-600">
                        {{ conversionRate }}%
                    </div>
                </CardContent>
            </Card>

            <!-- Total Revenue (Cash) -->
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-base font-medium">Cash (€)</CardTitle>
                    <CardDescription>
                        Total revenue from invoices
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-emerald-600">
                        {{ formatCurrency(stats?.totalRevenue) }}
                    </div>
                </CardContent>
            </Card>

            <!-- Turnover -->
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-base font-medium">Turnover (€)</CardTitle>
                    <CardDescription>
                        Total business turnover
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-indigo-600">
                        {{ formatCurrency(stats?.totalRevenue) }}
                    </div>
                </CardContent>
            </Card>

            <!-- Net Profit -->
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-base font-medium">Net (€)</CardTitle>
                    <CardDescription>
                        Estimated net profit (30% of revenue)
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-cyan-600">
                        {{ formatCurrency((stats?.totalRevenue || 0) * 0.3) }}
                    </div>
                </CardContent>
            </Card>

            <!-- ROI -->
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-base font-medium">ROI (%)</CardTitle>
                    <CardDescription>
                        Return on investment percentage
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-pink-600">
                        {{ roi }}%
                    </div>
                </CardContent>
            </Card>

            <!-- Average Invoice -->
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-base font-medium">Avg. Invoice (€)</CardTitle>
                    <CardDescription>
                        Average invoice amount
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-teal-600">
                        {{ formatCurrency(stats?.averageInvoiceAmount) }}
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- ✅ Real Financial Line Chart -->
        <Card class="mb-8">
            <CardHeader>
                <CardTitle>Financial Overview</CardTitle>
                <CardDescription>Monthly revenue, expenses, and profit trends</CardDescription>
            </CardHeader>
            <CardContent>
                <div class="h-80">
                    <Line :data="lineChartData" :options="lineChartOptions" />
                </div>
            </CardContent>
        </Card>

        <!-- ✅ Real Doughnut Charts -->
        <div class="grid gap-6 md:grid-cols-2 mb-8">
            <!-- Sales Documents Chart -->
            <Card>
                <CardHeader>
                    <CardTitle>Sales Documents Overview</CardTitle>
                    <CardDescription>Distribution of quotes and invoices</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="h-64">
                        <Doughnut :data="doughnutChartData" :options="doughnutChartOptions" />
                    </div>
                </CardContent>
            </Card>

            <!-- Financial Analysis Chart -->
            <Card>
                <CardHeader>
                    <CardTitle>Financial Analysis</CardTitle>
                    <CardDescription>Revenue vs average invoice comparison</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="h-64">
                        <Doughnut :data="doughnutChartData2" :options="doughnutChartOptions2" />
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- ✅ Recent Quotes Table -->
        <Card v-if="recentQuotes.length > 0">
            <CardHeader>
                <CardTitle>Recent Quotes</CardTitle>
                <CardDescription>Latest quote activity in the system</CardDescription>
            </CardHeader>
            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Quote #</TableHead>
                            <TableHead>Customer</TableHead>
                            <TableHead>Date</TableHead>
                            <TableHead>Amount</TableHead>
                            <TableHead>Status</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="quote in paginatedQuotes" :key="quote.id_devis">
                            <TableCell class="font-medium">
                                {{ quote.quote_number }}
                            </TableCell>
                            <TableCell>
                                {{ quote.customer?.company_name || 'N/A' }}
                            </TableCell>
                            <TableCell>
                                {{ formatDate(quote.quote_date) }}
                            </TableCell>
                            <TableCell>
                                {{ formatCurrency(quote.total_amount) }}
                            </TableCell>
                            <TableCell>
                                <Badge :variant="getStatusBadgeVariant(quote.status)">
                                    {{ quote.status }}
                                </Badge>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <!-- Simple Pagination -->
                <div v-if="totalPages > 1" class="flex justify-center mt-4 space-x-2">
                    <button 
                        @click="currentPage = Math.max(1, currentPage - 1)"
                        :disabled="currentPage === 1"
                        class="px-3 py-1 text-sm bg-gray-200 rounded disabled:opacity-50 hover:bg-gray-300"
                    >
                        Previous
                    </button>
                    <span class="px-3 py-1 text-sm bg-gray-100 rounded">
                        {{ currentPage }} / {{ totalPages }}
                    </span>
                    <button 
                        @click="currentPage = Math.min(totalPages, currentPage + 1)"
                        :disabled="currentPage === totalPages"
                        class="px-3 py-1 text-sm bg-gray-200 rounded disabled:opacity-50 hover:bg-gray-300"
                    >
                        Next
                    </button>
                </div>
            </CardContent>
        </Card>

        <!-- ✅ No Data Message -->
        <Card v-else class="text-center py-8">
            <CardContent>
                <div class="text-gray-500">
                    <h3 class="text-lg font-medium mb-2">No Data Available</h3>
                    <p>Start creating quotes and invoices to see dashboard analytics.</p>
                    <p class="text-sm mt-2">Make sure you have quotes and invoices in your database.</p>
                </div>
            </CardContent>
        </Card>
    </Layout>
</template>

<style scoped>
/* Custom styles for better chart display */
.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
}

/* Ensure proper chart responsiveness */
canvas {
    max-height: 100% !important;
}

/* Card hover effects */
.card:hover {
    transform: translateY(-2px);
    transition: transform 0.2s ease-in-out;
}
</style>