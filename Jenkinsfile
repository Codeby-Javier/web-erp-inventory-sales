pipeline {
    agent any

    environment {
        // Defines the node version and php environment based on typical setups
        NODE_VERSION = '20'
        PHP_VERSION = '8.2'
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Backend Preparation') {
            steps {
                dir('Backend') {
                    sh 'composer install --no-dev --optimize-autoloader'
                }
            }
        }

        stage('Frontend Build') {
            steps {
                dir('frontend') {
                    sh 'npm install'
                    sh 'npm run build'
                }
            }
        }

        stage('Test & Syntax Check') {
            steps {
                dir('Backend') {
                    sh 'php -l index.php'
                    sh 'php -l router.php'
                    // Add phpunit or other test commands here if available
                }
            }
        }

        stage('Build Docker Images') {
            steps {
                script {
                    echo 'Building backend image...'
                    sh 'docker build -t my-repo/web-erp-backend:latest ./Backend'

                    echo 'Building frontend image...'
                    sh 'docker build -t my-repo/web-erp-frontend:latest ./frontend'
                }
            }
        }
    }

    post {
        success {
            echo 'Pipeline executed successfully!'
        }
        failure {
            echo 'Pipeline failed. Check the logs.'
        }
    }
}
