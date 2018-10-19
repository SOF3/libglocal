// This is a generated file. Not intended for manual editing.
package io.github.sof3.libglocal.intellij.psi.impl;

import java.util.List;
import org.jetbrains.annotations.*;
import com.intellij.lang.ASTNode;
import com.intellij.psi.PsiElement;
import com.intellij.psi.PsiElementVisitor;
import com.intellij.psi.util.PsiTreeUtil;
import static io.github.sof3.libglocal.intellij.parser.LgcElements.*;
import com.intellij.extapi.psi.ASTWrapperPsiElement;
import io.github.sof3.libglocal.intellij.psi.*;

public class LgcArithmeticPredicateImpl extends ASTWrapperPsiElement implements LgcArithmeticPredicate {

  public LgcArithmeticPredicateImpl(@NotNull ASTNode node) {
    super(node);
  }

  public void accept(@NotNull LgcVisitor visitor) {
    visitor.visitArithmeticPredicate(this);
  }

  public void accept(@NotNull PsiElementVisitor visitor) {
    if (visitor instanceof LgcVisitor) accept((LgcVisitor)visitor);
    else super.accept(visitor);
  }

  @Override
  @NotNull
  public LgcMathComparator getMathComparator() {
    return findNotNullChildByClass(LgcMathComparator.class);
  }

  @Override
  @Nullable
  public PsiElement getMathMod() {
    return findChildByType(MATH_MOD);
  }

  @Override
  @NotNull
  public PsiElement getMathSeparator() {
    return findNotNullChildByType(MATH_SEPARATOR);
  }

}