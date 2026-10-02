pipeline {
    agent any

    environment {
        AWS_REGION = 'us-west-2'
        AWS_ACCOUNT_ID = '308621094829'
        ECR_REPOSITORY = 'aws-php-cicd-capstone'
        IMAGE_TAG = '1.1'
        ECR_REGISTRY = "${AWS_ACCOUNT_ID}.dkr.ecr.${AWS_REGION}.amazonaws.com"
        ECR_IMAGE = "${ECR_REGISTRY}/${ECR_REPOSITORY}:${IMAGE_TAG}"
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Verify Tools') {
            steps {
                sh '''
                    set -e
                    aws --version
                    docker --version
                    aws sts get-caller-identity
                '''
            }
        }

        stage('Login to ECR') {
            steps {
                sh '''
                    set -e
                    aws ecr get-login-password --region "$AWS_REGION" |
                    docker login --username AWS --password-stdin "$ECR_REGISTRY"
                '''
            }
        }

        stage('Build Docker Image') {
            steps {
                sh '''
                    set -e
                    docker build -t "$ECR_IMAGE" .
                '''
            }
        }

        stage('Push Image to ECR') {
            steps {
                sh '''
                    set -e
                    docker push "$ECR_IMAGE"
                '''
            }
        }
    }

    post {
        success {
            echo 'PHP Docker image built and pushed to ECR successfully.'
        }
        failure {
            echo 'Pipeline failed. Check the stage logs for the error.'
        }
    }
}
