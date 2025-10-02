import { clsx } from "clsx";
import { twMerge } from "tailwind-merge";

export function cn(...inputs) {
    return twMerge(clsx(inputs));
}

export function valueUpdater(updaterOrValue, ref) {
    ref.value =
        typeof updaterOrValue === "function"
            ? updaterOrValue(ref.value)
            : updaterOrValue;
}

// Utility functions for sorting, status, and pagination

export function handleSort(field, sortField, sortDirection) {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
    } else {
        sortField.value = field;
        sortDirection.value = "asc";
    }
}

export function getStatusClass(status) {
    if (status === "Accepté" || status === "Acceptée")
        return "bg-green-100 text-green-800";
    if (status === "Rejeté" || status === "Rejetée")
        return "bg-red-100 text-red-800";
    if (status === "En cours") return "bg-blue-100 text-blue-800";
    if (status === "En attente") return "bg-yellow-100 text-yellow-800";
    return "";
}

export function getSortedData(data, sortField, sortDirection) {
    if (!sortField.value) return data;
    return [...data].sort((a, b) => {
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
}

export function getPaginatedData(sortedData, currentPage, itemsPerPage) {
    return sortedData.slice(
        (currentPage.value - 1) * itemsPerPage,
        currentPage.value * itemsPerPage
    );
}

export function getTotalPages(data, itemsPerPage) {
    return Math.ceil(data.length / itemsPerPage);
}
