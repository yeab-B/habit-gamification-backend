import React from "react";

export const FinancePage: React.FC = () => {
  return (
    <div className="space-y-6" id="finance-page">
      <div className="flex flex-col gap-2">
        <h1 className="text-3xl font-bold tracking-tight text-foreground">Personal Finance</h1>
        <p className="text-muted-foreground">Manage your budgets, track transactions, and align your savings goals.</p>
      </div>
      <div className="rounded-xl border border-border bg-card p-6 shadow-sm">
        <div className="h-60 rounded-lg border border-dashed border-border flex items-center justify-center text-sm text-muted-foreground">
          Finance Manager Dashboard & Transaction Ledger Placeholder
        </div>
      </div>
    </div>
  );
};

export default FinancePage;
