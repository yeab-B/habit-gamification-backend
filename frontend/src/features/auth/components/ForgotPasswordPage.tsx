import React from "react";
import { Link } from "react-router-dom";

export const ForgotPasswordPage: React.FC = () => {
  return (
    <div className="flex min-h-screen items-center justify-center bg-background px-4 py-12 sm:px-6 lg:px-8" id="forgot-password-page">
      <div className="w-full max-w-md space-y-8 border border-border bg-card p-8 rounded-lg shadow-lg">
        <div>
          <h2 className="mt-6 text-center text-3xl font-extrabold tracking-tight text-foreground">
            Reset Password
          </h2>
          <p className="mt-2 text-center text-sm text-muted-foreground">
            Enter your email to receive a password reset link.
          </p>
        </div>
        <div className="mt-8 space-y-6">
          <div className="rounded-md border border-dashed border-border p-4 text-center text-sm text-muted-foreground">
            Reset Form Placeholder
          </div>
          <div className="text-center text-sm">
            <Link to="/login" className="font-medium text-primary hover:underline">
              Back to login
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ForgotPasswordPage;
