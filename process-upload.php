<?php
include 'db.php';

if (isset($_FILES['quizfile'])) {
    $xmlContent = simplexml_load_file($_FILES['quizfile']['tmp_name']);
    $topic = $xmlContent['topic'];

    // Insert or get topic_id
    $stmt = $conn->prepare("SELECT id FROM quiz_topics WHERE topic_name = ?");
    $stmt->bind_param("s", $topic);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $insert = $conn->prepare("INSERT INTO quiz_topics (topic_name) VALUES (?)");
        $insert->bind_param("s", $topic);
        $insert->execute();
        $topic_id = $insert->insert_id;
    } else {
        $row = $result->fetch_assoc();
        $topic_id = $row['id'];

        // Optional: delete old questions
        $conn->query("DELETE FROM quiz_questions WHERE topic_id = $topic_id");
    }

    foreach ($xmlContent->question as $q) {
        $text = $q->text;
        $a = $q->optionA;
        $b = $q->optionB;
        $c = $q->optionC;
        $d = $q->optionD;
        $correct = $q->correct;

        $stmt = $conn->prepare("INSERT INTO quiz_questions (topic_id, question_text, optionA, optionB, optionC, optionD, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $topic_id, $text, $a, $b, $c, $d, $correct);
        $stmt->execute();
    }

    echo "Topic '$topic' uploaded successfully!";
}
?>