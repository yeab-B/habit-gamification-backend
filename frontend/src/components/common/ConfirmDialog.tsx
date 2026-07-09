import React from "react";
import { cn } from "@/utils";

interface ConfirmDialogProps {
  isOpen: boolean;
  title: string;
  description: string;
  confirmText?: string;
  cancelText?: string;
  onConfirm: () => void;
  onCancel: () => void;
  className?: string;
}

export const ConfirmDialog: React.FC<ConfirmDialogProps> = ({
  isOpen,
  title,
  description,
  confirmText = "Confirm",
  cancelText = "Cancel",
  onConfirm,
  onCancel,
  className,
}) => {
  if (!isOpen) return null;

  return (
    <div
      className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
      id="confirm-dialog"
    >
      <div
        className={cn(
          "w-full max-w-md bg-card border border-border rounded-lg shadow-lg p-6 space-y-4 animate-in fade-in zoom-in-95 duration-200 text-left",
          className
        )}
      >
        <div className="space-y-1">
          <h3 className="text-lg font-semibold leading-none tracking-tight text-foreground">
            {title}
          </h3>
          <p className="text-sm text-muted-foreground">{description}</p>
        </div>
        <div className="flex justify-end space-x-2 pt-2">
          <button
            onClick={onCancel}
            className="px-4 py-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground text-sm font-medium rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
          >
            {cancelText}
          </button>
          <button
            onClick={onConfirm}
            className="px-4 py-2 bg-destructive text-destructive-foreground hover:bg-destructive/90 text-sm font-medium rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-destructive focus:ring-offset-2"
          >
            {confirmText}
          </button>
        </div>
      </div>
    </div>
  );
};

export default ConfirmDialog;
