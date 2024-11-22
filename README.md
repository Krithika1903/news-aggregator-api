# Laravel News Aggregator

This project is a **news aggregator API** built with **Laravel**. The application fetches news articles from various sources, categorizes them, and allows users to customize their preferences for categories, sources, and authors.

## Setup Instructions

### Prerequisites:

Before setting up the project, ensure that the following are installed on your machine:

-**Docker**: Used for containerization of the application.
    - [Install Docker] https://docs.docker.com/desktop/setup/install/windows-install/#start-docker-desktop


### Steps to Run the Project
Follow the steps below to run the Laravel News Aggregator project using Docker.

1. **Clone the Repository**
    Start by cloning the repository:

    ```bash
        git clone https://github.com/Krithika1903/news-aggregator-api.git
        cd your-repo
    ```

2. **Set Up the Environment File** 
    Copy the `.env.example` file to `.env`:

    ```bash
        cp .env.example .env
    ```
    - Run below command to set application key
        ```bash
            docker-compose exec laravel-app php artisan key:generate
        ``` 

    - Modify the `.env` file to configure your database settings:(make sure that the .env file 
       details and  docker-compose.yml details are matched)
     - `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
    
    - The Docker Compose file (`docker-compose.yml`) contains the following default database settings:
     ```yaml
        DB_CONNECTION: mysql
        DB_HOST: laravel-db
        DB_PORT: 3306
        DB_DATABASE: news_aggregator_db
        DB_USERNAME: root
        DB_PASSWORD: root
     ```

    - **API Keys**: Obtain API keys from the respective platforms (NewsAPI, Guardian, and NYTimes) or use the default keys provided below:
        ```plaintext
        NEWS_API_KEY="fbc2941662d140fcb3167777db9048e8"
        GUARDIAN_API_KEY="c4c5fd5f-8ad4-41e2-aa8e-040d97ed3c48"
        NYT_API_KEY="zGh4KLe7iRKka3GYRvieedVAH1FkNGs6"
        ```

3. **Build and Start Docker Containers**
    After setting up the `.env` file, build the Docker containers:

    ```bash
    docker compose build
    ```
    Then, start the containers in the background:

    Once the build is done run

    ```bash
    docker-compose up -d
    ```

    This will:
   - Build the images defined in the `Dockerfile`.
   - Set up the MySQL database container. 

4. **Run Migrations and Seeders**

   To set up the database schema and seed it with initial data, run the following command:

    ```bash
    docker-compose exec laravel-app php artisan migrate --seed
    ```

5. **Stopping the Docker Containers**

   To stop and remove the Docker containers, run:

    ```bash
    docker-compose down
    ```

---

## Swagger API Documentation
1. **Link to Swagger API Docs**
    The API documentation is available on **SwaggerHub**. You can access the documentation here:
    - [News Aggregator API Documentation] (https://app.swaggerhub.com/apis/KRITHIKASALIAN55_1/news-aggregator_api/1.0.11)

    You can use the **"Try it out"** feature on SwaggerHub to interact with the API endpoints directly from the documentation.

2. **Alternative Option to Test API**

   If the **"Try it out"** feature is not working,or the free trial has ended you can download the YAML file and test the API in **Swagger Editor**:

   - Download the YAML file from : (https://drive.google.com/file/d/1Cy-aFJr2foTmHEJBCUkvl5h2OTPBeZpD/view).
   - Open/Import the YAML file in [Swagger Editor](https://editor.swagger.io/) and test the API endpoints there.

---


## Additional Notes

- The **Docker setup** uses **MySQL** as the default database. If you want to use a different database, modify both the `docker-compose.yml` and the `.env` file accordingly.
  
- **API Key Limits**: Each API platform (e.g., NewsAPI, Guardian, and NYTimes) has its own rate limits. You may need to sign up for your own API keys if the provided ones reach their limits.

- **Security**: The API uses **Laravel Sanctum** for token-based authentication. Endpoints that require authentication are protected by middleware, ensuring that only authorized users can access protected resources.

- **Error Handling**: The API uses **Laravel's built-in validation** and **custom error handling** to return meaningful error messages.

- **Running Tests**: To run the tests for the application:

    ```bash
    docker-compose exec laravel-app php artisan test
    ```

- **Data Aggregation**: Articles are fetched regularly using **Laravel scheduled commands** and stored in the local MySQL database for filtering and retrieval.

--- 

