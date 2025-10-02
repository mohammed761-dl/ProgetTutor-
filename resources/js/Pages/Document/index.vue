<script setup>
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
import Button from "@/components/ui/button/Button.vue";
import { ref, computed } from "vue";


const itemsPerPage = 10;
const currentPage = ref(1);
const sortField = ref(null);
const sortDirection = ref("asc");

function handleSort(field) {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
    } else {
        sortField.value = field;
        sortDirection.value = "asc";
    }
}

const sortedData = computed(() => {
    if (!sortField.value) return recentDocuments;
    return [...recentDocuments].sort((a, b) => {
        let aValue = a[sortField.value];
        let bValue = b[sortField.value];
        if (sortField.value === "createdDate") {
            aValue = new Date(aValue).getTime();
            bValue = new Date(bValue).getTime();
        }
        if (aValue < bValue) return sortDirection.value === "asc" ? -1 : 1;
        if (aValue > bValue) return sortDirection.value === "asc" ? 1 : -1;
        return 0;
    });
});

const paginatedData = computed(() =>
    sortedData.value.slice(
        (currentPage.value - 1) * itemsPerPage,
        currentPage.value * itemsPerPage
    )
);
const totalPages = Math.ceil(recentDocuments.length / itemsPerPage);

function getStatusClass(status) {
    if (status === "Validé") return "bg-green-100 text-green-800";
    if (status === "Rejeté") return "bg-red-100 text-red-800";
    if (status === "En cours") return "bg-blue-100 text-blue-800";
    if (status === "En attente") return "bg-yellow-100 text-yellow-800";
    return "";
}

// Demo data for PO status
const today = new Date();
const totalDocumentsCount = recentDocuments.length;
const monthlyTrend = "+12%"; // Example trend
const pendingPO = computed(() =>
    recentDocuments.filter((d) => d.status === "En attente PO")
);
const delayedDocuments = computed(() =>
    recentDocuments.filter(
        (d) =>
            d.status === "En attente PO" &&
            d.createdDate &&
            (today - new Date(d.createdDate)) / (1000 * 60 * 60 * 24) > 7
    )
);

// Card logic: update computed properties for new cards
const underReviewDocuments = computed(() =>
    recentDocuments.filter((d) => d.status === "En cours")
);
const monitoringDocuments = computed(() =>
    recentDocuments.filter(
        (d) =>
            d.status === "En attente PO" ||
            d.status === "Rejeté" ||
            d.status === "Validé"
    )
);

function printDocument(document) {
    // Placeholder for print logic
    alert(`Impression du document: ${document.id}`);
}
</script>

<template>
    <Layout>
        <div class="grid gap-6 md:grid-cols-3 mb-0">
            <!-- Total Documents Card -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle>Total Documents</CardTitle>
                        <CardDescription>
                            Total number of documents in the system
                        </CardDescription>
                    </div>
                    <div class="text-4xl">📄</div>
                </CardHeader>
                <CardContent>
                    <div class="text-3xl font-bold">
                        {{ totalDocumentsCount }}
                    </div>
                    <div class="text-sm text-green-600 mt-1">
                        {{ monthlyTrend }} this month
                    </div>
                </CardContent>
            </Card>
            <!-- Document Monitoring Card -->
            <Card>
                <CardHeader>
                    <CardTitle>Document Monitoring</CardTitle>
                    <CardDescription>
                        Documents being monitored (Pending PO, Rejected, or
                        Validated)
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-3xl font-bold">
                        {{ monitoringDocuments.length }}
                    </div>
                    <div class="text-sm text-muted-foreground mt-1">
                        <span v-if="monitoringDocuments.length">
                            Most recent:
                            {{ monitoringDocuments[0]?.client || "-" }}
                            ({{
                                monitoringDocuments[0]?.createdDate
                                    ? new Date(
                                          monitoringDocuments[0].createdDate
                                      ).toLocaleDateString("en-GB")
                                    : "-"
                            }})
                        </span>
                        <span v-else>No documents monitored</span>
                    </div>
                </CardContent>
            </Card>
            <!-- Documents Under Review Card -->
            <Card class="border-blue-500 border-2 bg-blue-50">
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle>Documents Under Review</CardTitle>
                        <CardDescription>
                            Documents currently under review
                        </CardDescription>
                    </div>
                    <div class="text-4xl">🕒</div>
                </CardHeader>
                <CardContent>
                    <div class="text-3xl font-bold text-blue-600">
                        {{ underReviewDocuments.length }}
                    </div>
                    <div
                        class="text-sm text-blue-700 mt-1"
                        v-if="underReviewDocuments.length"
                    >
                        Most recent:
                        {{ underReviewDocuments[0]?.client || "-" }}
                        ({{
                            underReviewDocuments[0]?.createdDate
                                ? new Date(
                                      underReviewDocuments[0].createdDate
                                  ).toLocaleDateString("en-GB")
                                : "-"
                        }})
                    </div>
                    <div class="text-sm text-muted-foreground mt-1" v-else>
                        No documents under review
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Recent Activity Table -->
        <Card class="mt-0">
            <CardHeader>
                <CardTitle>Recent Activity</CardTitle>
                <CardDescription>
                    List of recent documents and their status
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div class="overflow-x-auto h-96 overflow-y-auto">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>
                                    <button
                                        @click="handleSort('id')"
                                        class="font-semibold flex items-center gap-2"
                                    >
                                        Document ID
                                        <span v-if="sortField === 'id'">{{
                                            sortDirection === "asc" ? "▲" : "▼"
                                        }}</span>
                                    </button>
                                </TableHead>
                                <TableHead>
                                    <button
                                        @click="handleSort('owner')"
                                        class="font-semibold flex items-center gap-2"
                                    >
                                        Owner
                                        <span v-if="sortField === 'owner'">{{
                                            sortDirection === "asc" ? "▲" : "▼"
                                        }}</span>
                                    </button>
                                </TableHead>
                                <TableHead>
                                    <button
                                        @click="handleSort('client')"
                                        class="font-semibold flex items-center gap-2"
                                    >
                                        Client
                                        <span v-if="sortField === 'client'">{{
                                            sortDirection === "asc" ? "▲" : "▼"
                                        }}</span>
                                    </button>
                                </TableHead>
                                <TableHead>
                                    <button
                                        @click="handleSort('createdDate')"
                                        class="font-semibold flex items-center gap-2"
                                    >
                                        Creation Date
                                        <span
                                            v-if="sortField === 'createdDate'"
                                            >{{
                                                sortDirection === "asc"
                                                    ? "▲"
                                                    : "▼"
                                            }}</span
                                        >
                                    </button>
                                </TableHead>
                                <TableHead>
                                    <button
                                        @click="handleSort('dateValidation')"
                                        class="font-semibold flex items-center gap-2"
                                    >
                                        Validation Date
                                        <span
                                            v-if="
                                                sortField === 'dateValidation'
                                            "
                                            >{{
                                                sortDirection === "asc"
                                                    ? "▲"
                                                    : "▼"
                                            }}</span
                                        >
                                    </button>
                                </TableHead>
                                <TableHead>
                                    <button
                                        @click="handleSort('validateur')"
                                        class="font-semibold flex items-center gap-2"
                                    >
                                        Validator
                                        <span
                                            v-if="sortField === 'validateur'"
                                            >{{
                                                sortDirection === "asc"
                                                    ? "▲"
                                                    : "▼"
                                            }}</span
                                        >
                                    </button>
                                </TableHead>
                                <TableHead>
                                    <button
                                        @click="handleSort('status')"
                                        class="font-semibold flex items-center gap-2"
                                    >
                                        Status
                                        <span v-if="sortField === 'status'">{{
                                            sortDirection === "asc" ? "▲" : "▼"
                                        }}</span>
                                    </button>
                                </TableHead>
                                <TableHead>
                                    <button
                                        @click="handleSort('commentaire')"
                                        class="font-semibold flex items-center gap-2"
                                    >
                                        Comment
                                        <span
                                            v-if="sortField === 'commentaire'"
                                            >{{
                                                sortDirection === "asc"
                                                    ? "▲"
                                                    : "▼"
                                            }}</span
                                        >
                                    </button>
                                </TableHead>
                                <TableHead> Action </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="document in paginatedData"
                                :key="document.id"
                            >
                                <TableCell>{{ document.id }}</TableCell>
                                <TableCell>{{ document.owner }}</TableCell>
                                <TableCell>{{ document.client }}</TableCell>
                                <TableCell>{{
                                    new Date(
                                        document.createdDate
                                    ).toLocaleDateString("en-GB")
                                }}</TableCell>
                                <TableCell>{{
                                    document.dateValidation
                                        ? new Date(
                                              document.dateValidation
                                          ).toLocaleDateString("en-GB")
                                        : ""
                                }}</TableCell>
                                <TableCell>{{ document.validateur }}</TableCell>
                                <TableCell
                                    ><Badge
                                        :class="getStatusClass(document.status)"
                                        >{{ document.status }}</Badge
                                    ></TableCell
                                >
                                <TableCell>{{
                                    document.commentaire
                                }}</TableCell>
                                <TableCell>
                                    <Button
                                        size="sm"
                                        @click="printDocument(document)"
                                        >Print</Button
                                    >
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <!-- Pagination -->
                <div class="flex items-center justify-between space-x-2 py-4">
                    <div class="text-sm text-muted-foreground">
                        Showing
                        {{ (currentPage - 1) * itemsPerPage + 1 }} to
                        {{
                            Math.min(
                                currentPage * itemsPerPage,
                                recentDocuments.length
                            )
                        }}
                        of {{ recentDocuments.length }} results
                    </div>
                    <div class="flex items-center space-x-2">
                        <button
                            class="btn btn-outline btn-sm"
                            @click="currentPage--"
                            :disabled="currentPage === 1"
                        >
                            Previous
                        </button>
                        <div class="flex items-center space-x-1">
                            <button
                                v-for="page in totalPages"
                                :key="page"
                                class="btn btn-sm"
                                :class="
                                    currentPage === page
                                        ? 'btn-primary'
                                        : 'btn-outline'
                                "
                                @click="currentPage = page"
                            >
                                {{ page }}
                            </button>
                        </div>
                        <button
                            class="btn btn-outline btn-sm"
                            @click="currentPage++"
                            :disabled="currentPage === totalPages"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </CardContent>
        </Card>
    </Layout>
</template>
