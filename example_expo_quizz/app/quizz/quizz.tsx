import { useRouter } from "expo-router";
import { Pressable, ScrollView, Text } from "react-native";

const Quizz = () => {
  const router = useRouter();
  return (
    <ScrollView>
      <Pressable onPress={() => router.push("/quizz/hardcore")}>
        <Text>Je préfère la difficulté</Text>
      </Pressable>
      <Pressable onPress={() => router.push("/quizz/easy")}>
        <Text>Je préfère la facilité</Text>
      </Pressable>
    </ScrollView>
  );
};

export default Quizz;
