import { ArrowRight, Users, MessageSquare, Clock } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Link } from "@inertiajs/react";

export default function Welcome() {
    return (
    <div className="min-h-screen bg-background text-foreground">
      {/* Header */}
      <header className="bg-white/80 dark:bg-neutral-900/80 backdrop-blur-sm border-b border-gray-200 dark:border-neutral-800">
        <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center py-4">
            <div className="flex items-center space-x-3">
              <div className="w-10 h-10 bg-blue-600 dark:bg-blue-500 rounded-xl flex items-center justify-center">
                <MessageSquare className="h-6 w-6 text-white" />
              </div>
              <h1 className="text-2xl font-bold text-gray-900 dark:text-white">ClinicPing</h1>
            </div>
            <Link href="/dashboard">
              <Button className="bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white">
                Try Demo
                <ArrowRight className="ml-2 h-4 w-4" />
              </Button>
                            </Link>
          </div>
        </div>
                </header>

      {/* Hero Section */}
      <section className="py-16 px-4 sm:px-6 lg:px-8">
        <div className="max-w-4xl mx-auto text-center">
          <h1 className="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-6">
            Send Medical Forms via SMS
            <span className="text-blue-600 dark:text-blue-400"> to Your Patients</span>
          </h1>
          <p className="text-xl text-gray-600 dark:text-gray-300 mb-8 max-w-2xl mx-auto">
            Replace costly third-party services with this simple admin dashboard. 
            Send forms, track responses, and manage patient data all in one place.
          </p>
          <Link href="/dashboard">
            <Button size="lg" className="bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white px-8 py-4 text-lg">
              Try the Live Demo
              <ArrowRight className="ml-2 h-5 w-5" />
            </Button>
          </Link>
          <p className="text-sm text-gray-500 dark:text-gray-400 mt-4">No credit card required • Pre-loaded with sample data</p>
        </div>
      </section>

      {/* Quick Feature Overview */}
      <section className="py-12 bg-white dark:bg-neutral-900">
        <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid md:grid-cols-3 gap-6 text-center">
            <div>
              <div className="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mx-auto mb-3">
                <MessageSquare className="h-6 w-6 text-blue-600 dark:text-blue-400" />
              </div>
              <h3 className="font-semibold text-gray-900 dark:text-white mb-2">Send SMS Forms</h3>
              <p className="text-gray-600 dark:text-gray-300 text-sm">Instantly send medical forms to patients via text message</p>
            </div>
            <div>
              <div className="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mx-auto mb-3">
                <Users className="h-6 w-6 text-green-600 dark:text-green-400" />
                        </div>
              <h3 className="font-semibold text-gray-900 dark:text-white mb-2">Manage Patients</h3>
              <p className="text-gray-600 dark:text-gray-300 text-sm">Keep track of patient information and appointment status</p>
                        </div>
            <div>
              <div className="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mx-auto mb-3">
                <Clock className="h-6 w-6 text-purple-600 dark:text-purple-400" />
                </div>
              <h3 className="font-semibold text-gray-900 dark:text-white mb-2">Track Progress</h3>
              <p className="text-gray-600 dark:text-gray-300 text-sm">See who received forms and completion status in real-time</p>
            </div>
          </div>
        </div>
      </section>

      {/* Simple CTA */}
      <section className="py-16 bg-gray-50 dark:bg-neutral-950">
        <div className="max-w-2xl mx-auto text-center px-4 sm:px-6 lg:px-8">
          <h2 className="text-2xl font-bold text-gray-900 dark:text-white mb-4">
            See it in action
          </h2>
          <p className="text-gray-600 dark:text-gray-300 mb-6">
            The demo includes sample patients and shows how the SMS workflow would work in your practice.
          </p>
          <Link href="/dashboard">
            <Button size="lg" className="bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white px-8 py-3">
              Open Dashboard Demo
              <ArrowRight className="ml-2 h-5 w-5" />
            </Button>
          </Link>
        </div>
      </section>
    </div>
    );
}
